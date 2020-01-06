<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use stdClass;
use Traversable;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying bookings.
 *
 * @since [*next-version*]
 */
class QueryBookingsHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /**
     * The SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface|null
     */
    protected $statusCountsRm;

    /**
     * The booking statuses.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $statuses;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface         $controller     The booking resource controller.
     * @param SelectCapableInterface|null $statusCountsRm A SELECT resource model for booking status counts.
     * @param array|stdClass|Traversable  $statuses       The list of all booking statuses.
     */
    public function __construct(ControllerInterface $controller, $statusCountsRm, $statuses)
    {
        $this->_setController($controller);
        $this->_setStatusCountsRm($statusCountsRm);
        $this->_setStatuses($statuses);
    }

    /**
     * Sets the SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface|null The SELECT booking status counts resource model instance.
     */
    protected function _getStatusCountsRm()
    {
        return $this->statusCountsRm;
    }

    /**
     * Retrieves the SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface|null $statusCountsRm The SELECT booking status counts resource model instance.
     */
    protected function _setStatusCountsRm($statusCountsRm)
    {
        if ($statusCountsRm !== null && !($statusCountsRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $statusCountsRm
            );
        }

        $this->statusCountsRm = $statusCountsRm;
    }

    /**
     * Retrieves the list of booking statuses.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable The list of booking statuses.
     */
    protected function _getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Sets the list of booking statuses.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $statuses The list of booking statuses.
     */
    protected function _setStatuses($statuses)
    {
        $this->statuses = $this->_normalizeArray($statuses);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _handle(WP_REST_Request $request)
    {
        $bookings = $this->_getController()->get($request);
        $bookings = iterator_to_array($bookings);
        $count    = count($bookings);

        $response = [
            'items' => $bookings,
            'count' => $count,
        ];

        $statusCountsRm = $this->_getStatusCountsRm();

        if ($statusCountsRm !== null) {
            $statusCounts = [];
            foreach ($statusCountsRm->select() as $_status) {
                $_name  = $this->_containerGet($_status, 'status');
                $_count = $this->_containerGet($_status, 'status_count');

                $statusCounts[$_name] = $_count;
            }

            $statuses = [];
            foreach ($this->_getStatuses() as $_status) {
                $statuses[$_status] = isset($statusCounts[$_status])
                    ? $statusCounts[$_status]
                    : 0;
            }

            $response['statuses'] = $statuses;
        }

        return new WP_REST_Response($response, 200);
    }
}
