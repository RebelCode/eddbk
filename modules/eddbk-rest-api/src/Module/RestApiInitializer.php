<?php

namespace RebelCode\EddBookings\RestApi\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;
use WP_Error;
use WP_REST_Request;

/**
 * Initializes the REST API.
 *
 * @since [*next-version*]
 */
class RestApiInitializer implements InvocableInterface
{
    /*
     * @since [*next-version*]
     */
    use DataStoreAwareContainerTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerAwareTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $restApiConfig
     * @param ContainerInterface|null                       $handlerContainer
     */
    public function __construct($restApiConfig, $handlerContainer)
    {
        $this->_setDataStore($restApiConfig);
        $this->_setContainer($handlerContainer);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $this->_initRestApi();
    }

    /**
     * Initializes the REST API.
     *
     * @since [*next-version*]
     */
    protected function _initRestApi()
    {
        $config = $this->_getDataStore();

        $namespace = $this->_containerGet($config, 'namespace');
        $routesCfg = $this->_containerGet($config, 'routes');
        $routes    = $this->_processRouteConfig($routesCfg);

        foreach ($routes as $_pattern => $_config) {
            $this->_registerRoute($namespace, $_pattern, $_config);
        }
    }

    /**
     * Process the route config and prepares it for registration.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $config The configuration of the routes.
     *
     * @return array The processed config.
     */
    protected function _processRouteConfig($config)
    {
        $routes = [];

        foreach ($config as $_methodConfig) {
            $_pattern = $this->_containerGet($_methodConfig, 'pattern');
            $_methods = $this->_containerGet($_methodConfig, 'methods');
            $_handler = $this->_containerGet($_methodConfig, 'handler');

            // Get authorization validator key
            $_authValKey = $this->_containerHas($_methodConfig, 'authval')
                ? $this->_containerGet($_methodConfig, 'authval')
                : null;
            // Get authorization validator from container
            $_authVal = $_authValKey !== null
                ? $this->_getContainer()->get($_authValKey)
                : null;

            if (!isset($routes[$_pattern])) {
                $routes[$_pattern] = [];
            }

            $_finalConfig = [
                'methods'             => $this->_normalizeArray($_methods),
                'callback'            => $this->_getContainer()->get($_handler),
                'permission_callback' => function ($request) use ($_authVal) {
                    return $this->_isAuthorized($_authVal, $request);
                },
            ];

            $routes[$_pattern][] = $_finalConfig;
        }

        return $routes;
    }

    /**
     * The callback used to check if the requester is authorized to access the route.
     *
     * @since [*next-version*]
     *
     * @param ValidatorInterface|null $authValidator The validator to use to authorize, if any.
     * @param WP_REST_Request         $request       The request.
     *
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    protected function _isAuthorized($authValidator, $request)
    {
        try {
            if ($authValidator instanceof ValidatorInterface) {
                $authValidator->validate($request);
            }
        } catch (ValidationFailedExceptionInterface $exception) {
            return new WP_Error(
                'eddbk_rest_api_unauthorized',
                $this->__('You are not authorized to access this route'),
                [
                    'status'  => 401,
                    'reasons' => $exception->getValidationErrors(),
                ]
            );
        }

        return true;
    }

    /**
     * Registers an API route.
     *
     * @since [*next-version*]
     *
     * @param string $namespace The namespace.
     * @param string $pattern   The route pattern.
     * @param array  $args      The route args.
     */
    protected function _registerRoute($namespace, $pattern, $args)
    {
        register_rest_route($namespace, $pattern, $args);
    }
}
