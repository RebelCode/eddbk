<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Data\Container\ContainerSetCapableTrait;
use Dhii\Data\Exception\CouldNotTransitionExceptionInterface;
use Dhii\Data\StateAwareFactoryInterface;
use Dhii\Data\TransitionerAwareTrait;
use Dhii\Data\TransitionerInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\String\StringableInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\EntityManagerInterface;
use stdClass;
use Traversable;

/**
 * The API controller for bookings.
 *
 * @since [*next-version*]
 */
class BookingsController extends AbstractBaseCqrsController
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use TransitionerAwareTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use ParseIso8601CapableTrait;

    /* @since [*next-version*] */
    use ContainerSetCapableTrait;

    /**
     * The default number of items to return per page.
     *
     * @since [*next-version*]
     */
    const DEFAULT_NUM_ITEMS_PER_PAGE = 20;

    /**
     * The default page number.
     *
     * @since [*next-version*]
     */
    const DEFAULT_PAGE_NUMBER = 1;

    /**
     * The bookings entity manager.
     *
     * @since [*next-version*]
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The clients controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $clientsController;

    /**
     * The factory to use for creating state-aware bookings.
     *
     * @since [*next-version*]
     *
     * @var StateAwareFactoryInterface
     */
    protected $stateAwareFactory;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface           $iteratorFactory     The iterator factory to use for the results.
     * @param StateAwareFactoryInterface $bookingFactory      The booking factory.
     * @param TransitionerInterface      $bookingTransitioner The booking transitioner.
     * @param SelectCapableInterface     $selectRm            The SELECT bookings resource model.
     * @param EntityManagerInterface     $entityManager       The bookings entity manager.
     * @param object                     $exprBuilder         The expression builder.
     * @param ControllerInterface        $clientsController   The clients controller.
     */
    public function __construct(
        FactoryInterface $iteratorFactory,
        StateAwareFactoryInterface $bookingFactory,
        TransitionerInterface $bookingTransitioner,
        SelectCapableInterface $selectRm,
        EntityManagerInterface $entityManager,
        $exprBuilder,
        ControllerInterface $clientsController = null
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setTransitioner($bookingTransitioner);
        $this->_setSelectRm($selectRm);
        $this->_setExprBuilder($exprBuilder);

        $this->stateAwareFactory = $bookingFactory;
        $this->clientsController = $clientsController;
        $this->entityManager     = $entityManager;
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
            : static::DEFAULT_NUM_ITEMS_PER_PAGE;
        $numPerPage = $this->_normalizeInt($numPerPage);

        // Get page number
        $pageNum = $this->_containerHas($params, 'page')
            ? $this->_containerGet($params, 'page')
            : static::DEFAULT_PAGE_NUMBER;
        $pageNum = $this->_normalizeInt($pageNum);

        if ($numPerPage < 1) {
            throw $this->_createControllerException($this->__('Invalid number of items per page'), 400, null, $this);
        }

        if ($pageNum < 1) {
            throw $this->_createControllerException($this->__('Invalid page number'), 400, null, $this);
        }

        // Calculate query offset
        $offset = ($pageNum - 1) * $numPerPage;

        return $selectRm->select($this->_buildSelectCondition($params), [], $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * Overrides to attempt to transition the booking before insertion.
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        // Create state-aware booking from params
        $booking = $this->stateAwareFactory->make([
            StateAwareFactoryInterface::K_DATA => $this->_buildInsertRecord($params),
        ]);

        if (empty($booking)) {
            throw $this->_createControllerException($this->__('Booking data cannot be empty'), 400, null, $this);
        }

        // Insert into database
        $id = $this->entityManager->add($booking->getState());
        // Re-fetch from database
        $bookingData = $this->entityManager->get($id);

        try {
            // Read the "transition" from the request params
            $transition = $this->_containerGet($params, 'transition');
            // Create state-aware booking from data retrieved from DB
            $booking = $this->stateAwareFactory->make([
                StateAwareFactoryInterface::K_DATA => $bookingData,
            ]);
            // Attempt transition
            $booking = $this->_getTransitioner()->transition($booking, $transition);

            // Update the booking in storage after transitioning
            $this->entityManager->update($id, $booking->getState());

            // Respond with the booking info
            return $this->_get(['id' => $id]);
        } catch (CouldNotTransitionExceptionInterface $transitionEx) {
            // If transition failed, delete the booking
            $this->entityManager->delete($id);
            // Get the transition failure messages from the exception
            $errors = $this->_getTransitionFailureMessages($transitionEx);
            // and throw a controller exception
            throw $this->_createControllerException(
                $transitionEx->getMessage(), 403, $transitionEx, $this, [
                    'errors' => $this->_normalizeArray($errors),
                ]
            );
        } catch (NotFoundExceptionInterface $notFoundEx) {
            throw $this->_createControllerException(
                $this->__('Must provide an initial "transition" as either "draft" or "cart"'), 400, $notFoundEx, $this
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Cannot PUT a booking - use PATCH'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException($this->__('Missing booking `id` in request'), 400, $exception,
                $this);
        }

        $bookingData = $this->entityManager->get($id);
        $bookingData = $this->_normalizeIterable($bookingData);

        // Prepare change set
        $changeSet = $this->_buildUpdateChangeSet($params);
        // Create state-aware booking with the booking data patched against the change set in the request
        $booking = $this->stateAwareFactory->make([
            StateAwareFactoryInterface::K_DATA => $this->_patchBookingData($bookingData, $changeSet),
        ]);

        // If the transition was given in the request
        if ($this->_containerHas($params, 'transition')) {
            try {
                // Get transition from request params
                $transition = $this->_containerGet($params, 'transition');
                // Attempt transition on booking
                $booking = $this->_getTransitioner()->transition($booking, $transition);
                // Update the booking
                $this->entityManager->update($id, $booking->getState());
            } catch (CouldNotTransitionExceptionInterface $exception) {
                $errors = $this->_getTransitionFailureMessages($exception);

                throw $this->_createControllerException(
                    $this->__('Failed to transition the booking'), 500, $exception, $this, [
                        'errors' => $this->_normalizeArray($errors),
                    ]
                );
            }
        }

        return $this->_get(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException($this->__('Missing booking `id` in request'), 400, $exception,
                $this);
        }

        $this->entityManager->delete($id);

        return [];
    }

    /**
     * Patches the given booking data with a change set.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $bookingData The booking data.
     * @param array|stdClass|Traversable $changeSet   The change set.
     *
     * @return array|stdClass|Traversable The patched data.
     */
    protected function _patchBookingData($bookingData, $changeSet)
    {
        $patched = $this->_normalizeArray($bookingData);

        foreach ($changeSet as $_key => $_val) {
            $patched[$_key] = $_val;
        }

        return $patched;
    }

    /**
     * Retrieves the transition failure messages from a transition failure exception.
     *
     * @since [*next-version*]
     *
     * @param CouldNotTransitionExceptionInterface $exception The transition failure exception.
     *
     * @return array|Stringable[]|string[]|Traversable The transition errors.
     */
    protected function _getTransitionFailureMessages(CouldNotTransitionExceptionInterface $exception)
    {
        $validationEx = $exception;
        // Move up the stack until a validation exception is found
        while ($validationEx !== null && !($validationEx instanceof ValidationFailedExceptionInterface)) {
            $validationEx = $validationEx->getPrevious();
        }

        // Get the errors from the validation exception, if found
        $errors = ($validationEx instanceof ValidationFailedExceptionInterface)
            ? $validationEx->getValidationErrors()
            : [];

        return $errors;
    }

    /**
     * {@inheritdoc}
     *
     * Extends the condition building to add query conditions for searching by clients.
     *
     * @since [*next-version*]
     */
    protected function _buildSelectCondition($params)
    {
        $condition = parent::_buildSelectCondition($params);

        // Add condition to search by client
        if ($this->_containerHas($params, 'search')) {
            $search    = $this->_containerGet($params, 'search');
            $condition = $this->_addClientsSearchCondition($condition, $search);
        }

        // Add condition to filter by month
        if ($this->_containerHas($params, 'month')) {
            $month     = $this->_containerGet($params, 'month');
            $condition = $this->_addMonthFilterCondition($condition, $month);
        }

        return $condition;
    }

    /**
     * Adds a condition to filter bookings by month.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition The condition to add to.
     * @param int|string|StringableInterface  $month     The month index.
     *
     * @return LogicalExpressionInterface The new condition.
     */
    protected function _addMonthFilterCondition($condition, $month)
    {
        $month = $this->_normalizeInt($month);

        $b = $this->exprBuilder;

        $monthCondition = $b->eq(
            $b->fn('month', $b->fn('from_unixtime', $b->ef('booking', 'start'))),
            $b->lit($month)
        );

        return ($condition !== null)
            ? $b->and($condition, $monthCondition)
            : $monthCondition;
    }

    /**
     * Adds a client search condition to an existing query condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition The condition to add to.
     * @param string|StringableInterface      $search    The client search string.
     *
     * @return LogicalExpressionInterface The new condition.
     */
    protected function _addClientsSearchCondition($condition, $search)
    {
        $clients   = $this->clientsController->get(['search' => $search]);
        $clientIds = [];

        foreach ($clients as $_client) {
            $clientIds[] = $this->_containerGet($_client, 'id');
        }

        return $this->_addQueryCondition($condition, 'booking', 'client_id', $clientIds, 'in');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSelectConditionParamMapping()
    {
        return [
            'id'       => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'id',
            ],
            'start'    => [
                'compare'   => 'gte',
                'entity'    => 'booking',
                'field'     => 'start',
                'transform' => [$this, '_parseIso8601'],
            ],
            'end'      => [
                'compare'   => 'lte',
                'entity'    => 'booking',
                'field'     => 'end',
                'transform' => [$this, '_parseIso8601'],
            ],
            'status'   => [
                'compare'   => 'in',
                'entity'    => 'booking',
                'field'     => 'status',
                'transform' => function ($status) {
                    return ($status !== null)
                        ? explode(',', $status)
                        : null;
                },
            ],
            'service'  => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'service_id',
            ],
            'resource' => [
                'compare' => 'in',
                'entity'  => 'booking',
                'field'   => 'resource_ids',
            ],
            'client'   => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'client_id',
            ],
            'payment'  => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'payment_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Unused, since an entity manager is used to update bookings.
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
     * Unused, since an entity manager is used to delete bookings.
     *
     * @since [*next-version*]
     */
    protected function _getDeleteConditionParamMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getInsertParamFieldMapping()
    {
        return [
            'start'    => [
                'field'     => 'start',
                'required'  => true,
                'transform' => [$this, '_parseIso8601'],
            ],
            'end'      => [
                'field'     => 'end',
                'required'  => true,
                'transform' => [$this, '_parseIso8601'],
            ],
            'service'  => [
                'field'    => 'service_id',
                'required' => true,
            ],
            'resources' => [
                'field'    => 'resource_ids',
                'required' => true,
            ],
            'client'   => [
                'field'    => 'client_id',
                'required' => false,
            ],
            'clientTz' => [
                'field'    => 'client_tz',
                'required' => false,
            ],
            'payment'  => [
                'field'    => 'payment_id',
                'required' => false,
            ],
            'notes'    => [
                'field'    => 'admin_notes',
                'required' => false,
                'default'  => '',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateParamFieldMapping()
    {
        return [
            'start'    => [
                'field'     => 'start',
                'transform' => function ($value) {
                    if (empty($value)) {
                        throw $this->_createInvalidArgumentException(
                            $this->__('Booking start time cannot be an empty value'), null, null, $value
                        );
                    }

                    return $this->_parseIso8601($value);
                },
            ],
            'end'      => [
                'field'     => 'end',
                'transform' => function ($value) {
                    if (empty($value)) {
                        throw $this->_createInvalidArgumentException(
                            $this->__('Booking end time cannot be an empty value'), null, null, $value
                        );
                    }

                    return $this->_parseIso8601($value);
                },
            ],
            'service'  => [
                'field'     => 'service_id',
                'transform' => function ($value) {
                    if (empty($value)) {
                        throw $this->_createInvalidArgumentException(
                            $this->__('Service ID cannot be an empty value'), null, null, $value
                        );
                    }

                    return $value;
                },
            ],
            'resources' => [
                'field'     => 'resource_ids',
                'transform' => function ($value) {
                    if (empty($value)) {
                        throw $this->_createInvalidArgumentException(
                            $this->__('Resources list cannot be an empty value'), null, null, $value
                        );
                    }

                    return $value;
                },
            ],
            'client'   => [
                'field'     => 'client_id',
                'transform' => function ($value) {
                    if (empty($value)) {
                        throw $this->_createInvalidArgumentException(
                            $this->__('Client ID cannot be an empty value'), null, null, $value
                        );
                    }

                    return $value;
                },
            ],
            'clientTz' => [
                'field'   => 'client_tz',
                'default' => 'UTC',
            ],
            'payment'  => [
                'field'   => 'payment_id',
                'default' => '',
            ],
            'notes'    => [
                'field'   => 'admin_notes',
                'default' => '',
            ],
        ];
    }
}
