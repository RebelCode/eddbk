<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Services;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API handler for deleting services.
 *
 * @since [*next-version*]
 */
class DeleteServiceHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $servicesController The services controller.
     */
    public function __construct(ControllerInterface $servicesController)
    {
        $this->_setController($servicesController);
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
