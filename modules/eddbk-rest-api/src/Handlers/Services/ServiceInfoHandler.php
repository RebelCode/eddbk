<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Services;

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
 * Handles the REST API endpoint for retrieving the info for a particular service.
 *
 * @since [*next-version*]
 */
class ServiceInfoHandler extends AbstractWpRestApiHandler
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
    public function _handle(WP_REST_Request $request)
    {
        $services = $this->_getController()->get([
            'id' => ($id = $request['id']),
        ]);
        $services = $this->_normalizeArray($services);
        $count    = count($services);

        if ($count === 0) {
            return new WP_Error(
                'eddbk_service_not_found',
                $this->__('No service found for id "%s"', [$id]),
                ['status' => 404]
            );
        }

        if ($count > 1) {
            return new WP_Error(
                'eddbk_service_query_error',
                $this->__('Found %d matching services', [$count]),
                ['status' => 500]
            );
        }

        return new WP_REST_Response($services[0], 200);
    }
}
