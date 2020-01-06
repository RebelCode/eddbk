<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use ArrayIterator;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use IteratorIterator;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Abstract base functionality for REST API controllers that use CQRS resource models.
 *
 * Provides awareness of CQRS resource models, as well as condition building and field mapping logic.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseCqrsController extends AbstractBaseController implements ControllerInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The key for param type in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_TYPE = 'type';

    /**
     * The key for param entity in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_ENTITY = 'entity';

    /**
     * The key for param field in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_FIELD = 'field';

    /**
     * The key for param comparison mode in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_COMPARE = 'compare';

    /**
     * The key for param required flag in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_REQUIRED = 'required';

    /**
     * The key for param default value in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_DEFAULT = 'default';

    /**
     * The key for param transformation function in configuration.
     *
     * @since [*next-version*]
     */
    const K_PARAM_TRANSFORM = 'transform';

    /**
     * The SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $selectRm;

    /**
     * The INSERT resource model.
     *
     * @since [*next-version*]
     *
     * @var InsertCapableInterface
     */
    protected $insertRm;

    /**
     * The UPDATE resource model.
     *
     * @since [*next-version*]
     *
     * @var UpdateCapableInterface
     */
    protected $updateRm;

    /**
     * The DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $deleteRm;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * Retrieves the SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface|null The resource model instance, if any.
     */
    protected function _getSelectRm()
    {
        return $this->selectRm;
    }

    /**
     * Sets the SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface|null $selectRm The resource model instance, if any.
     */
    protected function _setSelectRm($selectRm)
    {
        if ($selectRm !== null && !($selectRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $selectRm
            );
        }

        $this->selectRm = $selectRm;
    }

    /**
     * Retrieves the INSERT resource model.
     *
     * @since [*next-version*]
     *
     * @return InsertCapableInterface|null The resource model instance, if any.
     */
    protected function _getInsertRm()
    {
        return $this->insertRm;
    }

    /**
     * Sets the INSERT resource model.
     *
     * @since [*next-version*]
     *
     * @param InsertCapableInterface|null $insertRm The resource model instance, if any.
     */
    protected function _setInsertRm($insertRm)
    {
        if ($insertRm !== null && !($insertRm instanceof InsertCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an INSERT resource model'), null, null, $insertRm
            );
        }

        $this->insertRm = $insertRm;
    }

    /**
     * Retrieves the UPDATE resource model.
     *
     * @since [*next-version*]
     *
     * @return UpdateCapableInterface|null The resource model instance, if any.
     */
    protected function _getUpdateRm()
    {
        return $this->updateRm;
    }

    /**
     * Sets the UPDATE resource model.
     *
     * @since [*next-version*]
     *
     * @param UpdateCapableInterface|null $updateRm The resource model instance, if any.
     */
    protected function _setUpdateRm($updateRm)
    {
        if ($updateRm !== null && !($updateRm instanceof UpdateCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an UPDATE resource model'), null, null, $updateRm
            );
        }

        $this->updateRm = $updateRm;
    }

    /**
     * Retrieves the DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @return DeleteCapableInterface|null The DELETE resource model instance, if any.
     */
    protected function _getDeleteRm()
    {
        return $this->deleteRm;
    }

    /**
     * Sets the DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @param DeleteCapableInterface|null $deleteRm The DELETE source model insance, if any.
     */
    protected function _setDeleteRm($deleteRm)
    {
        if ($deleteRm !== null && !($deleteRm instanceof DeleteCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a DELETE resource model'), null, null, $deleteRm
            );
        }

        $this->deleteRm = $deleteRm;
    }

    /**
     * Retrieves the expression builder.
     *
     * @since [*next-version*]
     *
     * @return object|null The expression builder, if any.
     */
    protected function _getExprBuilder()
    {
        return $this->exprBuilder;
    }

    /**
     * Sets the expression builder.
     *
     * @since [*next-version*]
     *
     * @param object|null $exprBuilder The expression builder, if any.
     */
    protected function _setExprBuilder($exprBuilder)
    {
        $this->exprBuilder = $exprBuilder;
    }

    /**
     * Builds a logical expression for SELECT queries from a set of request params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The input parameters.
     *
     * @return LogicalExpressionInterface|null The built condition.
     */
    protected function _buildSelectCondition($params)
    {
        // The query condition
        $condition = null;

        foreach ($this->_getSelectConditionParamMapping() as $_param => $_info) {
            $_compare = $this->_containerGet($_info, static::K_PARAM_COMPARE);
            $_entity  = $this->_containerGet($_info, static::K_PARAM_ENTITY);
            $_field   = $this->_containerGet($_info, static::K_PARAM_FIELD);

            $_transform = $this->_containerHas($_info, static::K_PARAM_TRANSFORM)
                ? $this->_containerGet($_info, static::K_PARAM_TRANSFORM)
                : null;

            // Get the value
            $_value = $this->_containerHas($params, $_param) ? $this->_containerGet($params, $_param) : null;
            // Ensure it is not empty
            $_value = strlen($_value) > 0 ? $_value : null;

            // If a transform callback is given in the info for the param, invoke it
            $_value = ($_transform !== null) ? call_user_func_array($_transform, [$_value]) : $_value;

            $condition = $this->_addQueryCondition($condition, $_entity, $_field, $_value, $_compare);
        }

        return $condition;
    }

    /**
     * Builds a record from the request params, for insertion.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The request params.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The built record.
     */
    protected function _buildInsertRecord($params)
    {
        $recordData = [];

        foreach ($this->_getInsertParamFieldMapping() as $_param => $_mapping) {
            $field    = $this->_containerGet($_mapping, static::K_PARAM_FIELD);
            $required = $this->_containerGet($_mapping, static::K_PARAM_REQUIRED);
            $default  = $this->_containerHas($_mapping, static::K_PARAM_DEFAULT)
                ? $this->_containerGet($_mapping, static::K_PARAM_DEFAULT)
                : null;
            $transform  = $this->_containerHas($_mapping, static::K_PARAM_TRANSFORM)
                ? $this->_containerGet($_mapping, static::K_PARAM_TRANSFORM)
                : null;
            $hasParam = $this->_containerHas($params, $_param);
            $value    = $hasParam ? $this->_containerGet($params, $_param) : null;
            $hasParam = $hasParam && $value !== null && !empty($value);

            if (!$hasParam && $required) {
                throw $this->_createControllerException(
                    $this->__('A "%s" value must be specified', [$_param]), 400, null, $this
                );
            }

            if ($hasParam) {
                // Get the value
                $value = $this->_containerGet($params, $_param);
                // Transform it if a transformation callback is present in the mapping config
                $value = ($transform !== null) ? call_user_func_array($transform, [$value]) : $value;

                $recordData[$field] = $value;
            } else {
                $recordData[$field] = $default;
            }
        }

        return $recordData;
    }

    /**
     * Builds a change set from the request params, for updating.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The request params.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The built change set.
     */
    protected function _buildUpdateChangeSet($params)
    {
        $changeSet = [];

        foreach ($this->_getUpdateParamFieldMapping() as $_param => $_mapping) {
            // If param not in request params, skip
            if (!$this->_containerHas($params, $_param)) {
                continue;
            }

            $field     = $this->_containerGet($_mapping, static::K_PARAM_FIELD);
            $default   = $this->_containerHas($_mapping, static::K_PARAM_DEFAULT)
                ? $this->_containerGet($_mapping, static::K_PARAM_DEFAULT)
                : null;
            $transform = $this->_containerHas($_mapping, static::K_PARAM_TRANSFORM)
                ? $this->_containerGet($_mapping, static::K_PARAM_TRANSFORM)
                : null;

            // Get the value
            $value = $this->_containerHas($params, $_param) ? $this->_containerGet($params, $_param) : null;
            // Ensure it is not empty
            $value = !empty($value)? $value : null;
            // Transform it if a transformation callback is present in the mapping config
            $value = ($transform !== null) ? call_user_func_array($transform, [$value]) : $value;
            // Use default value if null
            $value = ($value === null) ? $default : $value;

            $changeSet[$field] = $value;
        }

        return $changeSet;
    }

    /**
     * Builds a logical expression for UPDATE queries from a set of request params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The input parameters.
     *
     * @return LogicalExpressionInterface|null The built condition.
     */
    protected function _buildUpdateCondition($params)
    {
        // The query condition
        $condition = null;

        foreach ($this->_getUpdateConditionParamMapping() as $_param => $_info) {
            $_compare   = $this->_containerGet($_info, static::K_PARAM_COMPARE);
            $_field     = $this->_containerGet($_info, static::K_PARAM_FIELD);
            $transform  = $this->_containerHas($_info, static::K_PARAM_TRANSFORM)
                ? $this->_containerGet($_info, static::K_PARAM_TRANSFORM)
                : null;

            // Get the value
            $_value = $this->_containerHas($params, $_param) ? $this->_containerGet($params, $_param) : null;
            // Ensure it is not empty
            $_value = strlen($_value) > 0 ? $_value : null;
            // Transform it if a transformation callback is present in the mapping config
            $_value = ($transform !== null) ? call_user_func_array($transform, [$_value]) : $_value;

            $condition = $this->_addQueryCondition($condition, null, $_field, $_value, $_compare);
        }

        return $condition;
    }

    /**
     * Builds a logical expression for DELETE queries from a set of request params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The input parameters.
     *
     * @return LogicalExpressionInterface|null The built condition.
     */
    protected function _buildDeleteCondition($params)
    {
        // The query condition
        $condition = null;

        foreach ($this->_getDeleteConditionParamMapping() as $_param => $_info) {
            $_compare   = $this->_containerGet($_info, static::K_PARAM_COMPARE);
            $_field     = $this->_containerGet($_info, static::K_PARAM_FIELD);
            $transform  = $this->_containerHas($_info, static::K_PARAM_TRANSFORM)
                ? $this->_containerGet($_info, static::K_PARAM_TRANSFORM)
                : null;

            // Get the value
            $_value = $this->_containerHas($params, $_param) ? $this->_containerGet($params, $_param) : null;
            // Ensure it is not empty
            $_value = strlen($_value) > 0 ? $_value : null;
            // Transform it if a transformation callback is present in the mapping config
            $_value = ($transform !== null) ? call_user_func_array($transform, [$_value]) : $_value;

            $condition = $this->_addQueryCondition($condition, null, $_field, $_value, $_compare);
        }

        return $condition;
    }

    /**
     * Adds a query condition to another root condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null  $condition The root condition.
     * @param string|Stringable                $entity    The entity name to use in the expression.
     * @param string|Stringable                $field     The name of field.
     * @param mixed                            $value     The query value.
     * @param string|Stringable|int|float|bool $compare   The comparison mode - the  expression builder method.
     *
     * @return LogicalExpressionInterface|null The amended condition.
     */
    protected function _addQueryCondition($condition, $entity, $field, $value, $compare = 'eq')
    {
        if ($value === null) {
            return $condition;
        }

        $queryCondition = $this->_createComparisonExpression($compare, $entity, $field, $value);

        // If query condition is null, return it as the query condition
        if ($condition === null) {
            return $queryCondition;
        }

        $exprBuilder = $this->_getExprBuilder();
        if ($exprBuilder === null) {
            throw $this->_createRuntimeException($this->__('The expression builder is null'));
        }

        // Otherwise, AND the existing condition with the query condition
        $condition = $this->_getExprBuilder()->and($condition, $queryCondition);

        return $condition;
    }

    /**
     * Creates a comparison expression.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                $type   The expression type.
     * @param string|Stringable                $entity The entity name.
     * @param string|Stringable                $field  The field name.
     * @param string|Stringable|int|float|bool $value  The comparison value.
     *
     * @return LogicalExpressionInterface
     */
    protected function _createComparisonExpression($type, $entity, $field, $value)
    {
        $b = $this->_getExprBuilder();
        if ($b === null) {
            throw $this->_createRuntimeException($this->__('The expression builder is null'));
        }

        $term1 = ($entity !== null)
            ? $b->ef($entity, $field)
            : $b->var($field);
        $term2 = is_array($value)
            ? $b->set($value)
            : $b->lit($value);

        return call_user_func_array([$b, $type], [$term1, $term2]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }

    /**
     * Retrieves the mapping between request parameters and CQRS entity fields for SELECT conditions.
     *
     * The information about the params is a mapping of input param names to containers as values.
     * The containers are expected to have three keys: 'compare', 'entity' and 'field'.
     * The 'compare' index should have the relational mode to use in the expression. The 'entity' and 'field' indexes
     * should map to the names of the entity field value to compare to.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getSelectConditionParamMapping();

    /**
     * Retrieves the INSERT mapping between input parameters and CQRS entity field comparison info.
     *
     * The mapping is expected to have request param names as keys, mapping to child maps as values.
     * Each child map must have at least a "field" mapping, which maps to the field name, and a "required" mapping,
     * which maps to a boolean that signifies whether the request param is required or not. Optionally, a "default"
     * mapping may be given that maps to a value, which is used for non-required parameters that are not specified.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getInsertParamFieldMapping();

    /**
     * Retrieves the UPDATE mapping between input parameters and CQRS entity field comparison info.
     *
     * The mapping is expected to have request param names as keys, mapping to the corresponding resource model field
     * names as values. Having multiple request params update the same field is possible, but later entries in the
     * mapping will have precedence.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getUpdateParamFieldMapping();

    /**
     * Retrieves the mapping between request parameters and CQRS entity fields for UPDATE conditions.
     *
     * The information about the params is a mapping of input param names to containers as values.
     * The containers are expected to have two keys: 'compare' and 'field'.
     * The 'compare' index should have the relational mode to use in the expression. The 'entity' and 'field' indexes
     * should map to the names of the entity field value to compare to.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getUpdateConditionParamMapping();

    /**
     * Retrieves the mapping between request parameters and CQRS entity fields for DELETE conditions.
     *
     * The information about the params is a mapping of input param names to containers as values.
     * The containers are expected to have two keys: 'compare' and 'field'.
     * The 'compare' index should have the relational mode to use in the expression. The 'entity' and 'field' indexes
     * should map to the names of the entity field value to compare to.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getDeleteConditionParamMapping();
}
