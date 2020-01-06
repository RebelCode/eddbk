<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Services;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying services.
 *
 * @since [*next-version*]
 */
class QueryServicesHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The services controller.
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
    protected function _handle(WP_REST_Request $request)
    {
        $services = $this->_getController()->get($request);
        $services = $this->_normalizeArray($services);

        $response = [
            'items' => $services,
            'count' => count($services),
        ];

        return new WP_REST_Response($response, 200);
    }
}
