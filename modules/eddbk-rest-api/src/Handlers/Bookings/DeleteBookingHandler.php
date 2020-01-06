<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API handler for deleting bookings.
 *
 * @since [*next-version*]
 */
class DeleteBookingHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $bookingsController The bookings controller.
     */
    public function __construct(ControllerInterface $bookingsController)
    {
        $this->_setController($bookingsController);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _handle(WP_REST_Request $request)
    {
        $response = $this->_getController()->delete($request);
        $response = $this->_normalizeArray($response);

        return new WP_REST_Response($response, 200);
    }
}
