<?php

namespace RebelCode\EddBookings\RestApi\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use RebelCode\EddBookings\RestApi\Controller\Exception\ControllerExceptionInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Abstract and common functionality for WordPress REST API handlers.
 *
 * @since [*next-version*]
 */
abstract class AbstractWpRestApiHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $request WP_REST_Request */
        $request = func_get_arg(0);

        try {
            return $this->_handle($request);
        } catch (ControllerExceptionInterface $controllerException) {
            $data           = $this->_normalizeArray($controllerException->getResponseData());
            $data['status'] = $controllerException->getCode();

            return new WP_Error('eddbk_rest_api_controller_error', $controllerException->getMessage(), $data);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_rest_api_error', $exception->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Handles the request and provides a response.
     *
     * @since [*next-version*]
     *
     * @param WP_REST_Request $request The request.
     *
     * @return WP_REST_Response|WP_Error The response data.
     */
    abstract protected function _handle(WP_REST_Request $request);
}
