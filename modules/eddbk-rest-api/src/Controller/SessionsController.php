<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Traversable;

/**
 * The REST API controller for sessions.
 *
 * @since [*next-version*]
 */
class SessionsController extends AbstractBaseCqrsController
{
    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use ParseIso8601CapableTrait;

    /**
     * The default page number.
     *
     * @since [*next-version*]
     */
    const DEFAULT_PAGE_NUMBER = 1;

    /**
     * The order in which to query and provide sessions.
     *
     * @since [*next-version*]
     *
     * @var OrderInterface[]|Traversable
     */
    protected $ordering;

    /**
     * The default number of items to return per page.
     *
     * @since [*next-version*]
     *
     * @var int|float|string|Stringable
     */
    protected $defaultNumPerPage;

    /**
     * The maximum (hard cap) number of items to return per page.
     *
     * @since [*next-version*]
     *
     * @var int|float|string|Stringable
     */
    protected $maxNumPerPage;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface             $iteratorFactory   The iterator factory to use for the results.
     * @param SelectCapableInterface       $selectRm          The SELECT sessions resource model.
     * @param OrderInterface[]|Traversable $ordering          The ordering in which to query and provide sessions.
     * @param object                       $exprBuilder       The expression builder.
     * @param int|float|string|Stringable  $defaultNumPerPage The default number of items to return per page.
     * @param int|float|string|Stringable  $maxNumPerPage     The maximum number of items to return per page.
     */
    public function __construct(
        $iteratorFactory,
        $selectRm,
        $ordering,
        $exprBuilder,
        $defaultNumPerPage,
        $maxNumPerPage
    ) {
        $this->_setSelectRm($selectRm);
        $this->_setExprBuilder($exprBuilder);
        $this->_setFactory($iteratorFactory);
        $this->ordering          = $ordering;
        $this->defaultNumPerPage = $defaultNumPerPage;
        $this->maxNumPerPage     = $maxNumPerPage;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _get($params = [])
    {
        $selectRm = $this->_getSelectRm();

        if ($selectRm === null) {
            throw $this->_createRuntimeException($this->__('The SELECT resource model is null'));
        }

        // Get number of items per page
        $numPerPage = $this->_containerHas($params, 'numItems')
            ? $this->_containerGet($params, 'numItems')
            : $this->defaultNumPerPage;
        $numPerPage = $this->_normalizeInt($numPerPage);
        $numPerPage = min($numPerPage, $this->maxNumPerPage);

        if ($numPerPage < 1) {
            throw $this->_createControllerException($this->__('Invalid number of items per page'), 400, null, $this);
        }

        // Get page number
        $pageNum = $this->_containerHas($params, 'page')
            ? $this->_containerGet($params, 'page')
            : static::DEFAULT_PAGE_NUMBER;
        $pageNum = $this->_normalizeInt($pageNum);

        if ($pageNum < 1) {
            throw $this->_createControllerException($this->__('Invalid page number'), 400, null, $this);
        }

        // Calculate query offset
        $offset = ($pageNum - 1) * $numPerPage;

        return $selectRm->select($this->_buildSelectCondition($params), $this->ordering, $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
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
    protected function _getSelectConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'entity'  => 'session',
                'field'   => 'id',
            ],
            'start' => [
                'compare'   => 'gt',
                'entity'    => 'session',
                'field'     => 'end',
                'transform' => [$this, '_parseIso8601'],
            ],
            'end' => [
                'compare'   => 'lt',
                'entity'    => 'session',
                'field'     => 'start',
                'transform' => [$this, '_parseIso8601'],
            ],
            'service' => [
                'compare' => 'eq',
                'entity'  => 'session',
                'field'   => 'service_id',
            ],
            'resource' => [
                'compare' => 'eq',
                'entity'  => 'session',
                'field'   => 'resource_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getInsertParamFieldMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateParamFieldMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateConditionParamMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getDeleteConditionParamMapping()
    {
        return [];
    }
}
