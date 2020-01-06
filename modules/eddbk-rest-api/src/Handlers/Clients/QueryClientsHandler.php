<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Clients;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying clients.
 *
 * @since [*next-version*]
 */
class QueryClientsHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The clients controller.
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
        $clients = $this->_getController()->get($request);
        $items   = $this->_normalizeArray($clients);

        $response = [
            'items' => $items,
            'count' => count($items),
        ];

        return new WP_REST_Response($response, 200);
    }
}
