<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Sessions;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying sessions.
 *
 * @since [*next-version*]
 */
class QuerySessionsHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The sessions controller.
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
        $sessions = $this->_getController()->get($request);
        $sessions = $this->_normalizeArray($sessions);

        $response = [
            'items' => $sessions,
            'count' => count($sessions),
        ];

        return new WP_REST_Response($response, 200);
    }
}
