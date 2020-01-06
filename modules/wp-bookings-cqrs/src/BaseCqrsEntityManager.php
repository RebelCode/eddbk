<?php

namespace RebelCode\Storage\Resource\WordPress;

use ArrayAccess;
use Dhii\Collection\MapInterface;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\EntityManagerInterface;
use stdClass;
use Traversable;

/**
 * A simple, base implementation of an entity manager that uses CQRS resource models.
 *
 * @since [*next-version*]
 */
class BaseCqrsEntityManager implements EntityManagerInterface
{
    /* @since [*next-version*] */
    use NormalizeStringableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

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
     * The factory for creating order object instances.
     *
     * @since [*next-version*]
     *
     * @var FactoryInterface
     */
    protected $orderFactory;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $selectRm     The SELECT resource model.
     * @param InsertCapableInterface $insertRm     The INSERT resource model.
     * @param UpdateCapableInterface $updateRm     The UPDATE resource model.
     * @param DeleteCapableInterface $deleteRm     The DELETE resource model.
     * @param FactoryInterface       $orderFactory The factory for creating order object instances.
     * @param object                 $exprBuilder  The expression builder.
     */
    public function __construct(
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        UpdateCapableInterface $updateRm,
        DeleteCapableInterface $deleteRm,
        FactoryInterface $orderFactory,
        $exprBuilder
    ) {
        $this->selectRm     = $selectRm;
        $this->insertRm     = $insertRm;
        $this->updateRm     = $updateRm;
        $this->deleteRm     = $deleteRm;
        $this->orderFactory = $orderFactory;
        $this->exprBuilder  = $exprBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        try {
            $results = $this->selectRm->select($this->_createIdExpression($id), null, 1);
        } catch (Exception $exception) {
            throw $this->_createContainerException(
                $this->__('An error occurred while retrieving the entity'), null, $exception, $this
            );
        }

        $results = $this->_normalizeArray($results);

        if (count($results) > 0 && $result = reset($results)) {
            return $this->_recordToEntity($result);
        }

        throw $this->_createNotFoundException(
            $this->__('Entity with id "%s" was not found', [$id]), null, null, $this, (string) $id
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        try {
            $this->get($id);

            return true;
        } catch (NotFoundExceptionInterface $exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function add($entity)
    {
        try {
            $ids = $this->insertRm->insert([
                $this->_entityToRecord($entity),
            ]);
        } catch (Exception $exception) {
            throw $this->_createRuntimeException(
                $this->__('Failed to add the entity'), null, $exception
            );
        }

        return reset($ids);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function delete($id)
    {
        try {
            $exception    = null;
            $affectedRows = $this->deleteRm->delete($this->_createIdExpression($id));
        } catch (Exception $exception) {
            $affectedRows = 0;
        }

        if ($affectedRows === 0 || $exception !== null) {
            throw $this->_createRuntimeException(
                $this->__('Failed to delete the entity with id "%s"', [$id]), null, $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function query($query = [], $limit = null, $offset = null, $orderBy = null, $desc = false)
    {
        $condition = $this->_queryToCondition($query);
        $ordering  = ($orderBy !== null) ? [$this->_createOrder($orderBy, $desc)] : null;
        $records   = $this->selectRm->select($condition, $ordering, $limit, $offset);
        $entities  = [];

        foreach ($records as $_record) {
            $entities[] = $this->_recordToEntity($_record);
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function set($id, $entity)
    {
        $this->update($id, $entity);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function update($id, $data)
    {
        $this->updateRm->update($this->_entityToRecord($data), $this->_createIdExpression($id), null, 1);
    }

    /**
     * Creates an expression for matching an entity by a given ID.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $id The ID.
     *
     * @return LogicalExpressionInterface The expression.
     */
    protected function _createIdExpression($id)
    {
        return $this->exprBuilder->eq(
            $this->exprBuilder->var('id'),
            $this->exprBuilder->lit($id)
        );
    }

    /**
     * Transforms entity data into record data that is usable for insertion and updates.
     *
     * @since [*next-version*]
     *
     * @param mixed $entity The entity.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The record data.
     */
    protected function _entityToRecord($entity)
    {
        return $entity;
    }

    /**
     * Transforms a record into an entity.
     *
     * @since [*next-version*]
     *
     * @param MapInterface $record The record.
     *
     * @return mixed The entity.
     */
    protected function _recordToEntity($record)
    {
        return $record;
    }

    /**
     * Transformers a query filter, as passed to {@link query()}, into a condition expression.
     *
     * @since [*next-version*]
     *
     * @see   query()
     *
     * @param array|stdClass|Traversable $query See {@link query()}.
     *
     * @return LogicalExpressionInterface The equivalent query as a condition expression.
     */
    protected function _queryToCondition($query)
    {
        // Prepare the expression builder and the expression terms array
        $b = $this->exprBuilder;
        $t = [];

        // Add an equivalence condition to the terms for each query filter
        foreach ($query as $_key => $_value) {
            $t[] = $this->_buildFieldQueryCompareExpression($_key, $_value);
        }

        // Create an AND expression with all the terms
        return count($t)
            ? call_user_func_array([$b, 'and'], $t)
            : null;
    }

    /**
     * Builds the query comparison expression for a particular field.
     *
     * @since [*next-version*]
     *
     * @param string $key The field key.
     * @param mixed $value The query value.
     *
     * @return LogicalExpressionInterface The field comparison expression.
     */
    protected function _buildFieldQueryCompareExpression($key, $value)
    {
        $b = $this->exprBuilder;

        return $b->eq(
            $b->var($key),
            $b->lit($value)
        );
    }

    /**
     * Creates an order instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $orderBy Optional name of the entity property by which to sort the returned entities,
     *                                   or null for no sorting. Applied before the $limit.
     * @param bool              $desc    Optional flag which if true will sort the entities in descending order. If
     *                                   false, which is default, returned entities are sorted in ascending order.
     *                                   Only applicable if the $orderBy param is not null.
     *
     * @return OrderInterface The created order instance.
     */
    protected function _createOrder($orderBy, $desc)
    {
        return $this->orderFactory->make([
            'field'     => $orderBy,
            'ascending' => !$desc,
        ]);
    }
}
