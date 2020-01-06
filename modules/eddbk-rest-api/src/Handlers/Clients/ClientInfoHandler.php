<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Clients;

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
 * Handles the REST API endpoint for retrieving the info for a particular client.
 *
 * @since [*next-version*]
 */
class ClientInfoHandler extends AbstractWpRestApiHandler
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
        $clients = $this->_getController()->get([
            'id' => ($id = $request['id']),
        ]);
        $clients = $this->_normalizeArray($clients);
        $count   = count($clients);

        if ($count === 0) {
            return new WP_Error(
                'eddbk_client_not_found',
                $this->__('No client found for id "%s"', [$id]),
                ['status' => 404]
            );
        }

        if ($count > 1) {
            return new WP_Error(
                'eddbk_client_query_error',
                $this->__('Found %d matching clients', [$count]),
                ['status' => 500]
            );
        }

        foreach ($clients as $client) {
            break;
        }

        return new WP_REST_Response($clients[0], 200);
    }
}
