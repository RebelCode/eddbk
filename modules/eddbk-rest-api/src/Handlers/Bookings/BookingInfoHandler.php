<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for retrieving the info for a particular booking.
 *
 * @since [*next-version*]
 */
class BookingInfoHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The booking resource controller.
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->_setController($controller);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _handle(WP_REST_Request $request)
    {
        $id       = $request->get_param('id');
        $bookings = $this->_getController()->get(['id' => $id]);
        $bookings = $this->_normalizeArray($bookings);
        $count    = count($bookings);

        if ($count === 0) {
            return new WP_Error(
                'eddbk_booking_not_found',
                $this->__('No booking found for id "%s"', [$id]),
                ['status' => 404]
            );
        }

        if ($count > 1) {
            return new WP_Error(
                'eddbk_booking_query_error',
                $this->__('Found %d matching bookings', [$count]),
                ['status' => 500]
            );
        }

        return new WP_REST_Response($bookings[0], 200);
    }
}
