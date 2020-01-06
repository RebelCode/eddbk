<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Services;

use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API handler for updating services.
 *
 * @since [*next-version*]
 */
class UpdateServiceHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

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
        $response = $this->_getController()->patch($request);
        $response = $this->_normalizeArray($response);

        if (empty($response)) {
            throw $this->_createRuntimeException($this->__('Failed to update service; response is empty'));
        }

        return new WP_REST_Response($response[0], 200);
    }
}
