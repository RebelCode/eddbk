<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Clients;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class CreateClientHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /**
     * The REST API config.
     *
     * @since [*next-version*]
     *
     * @var array|ArrayAccess|ContainerInterface|stdClass
     */
    protected $restApiConfig;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface                           $clientsController The clients controller.
     * @param array|stdClass|ArrayAccess|ContainerInterface $restApiConfig     The REST API config.
     */
    public function __construct(ControllerInterface $clientsController, $restApiConfig)
    {
        $this->_setController($clientsController);
        $this->_setRestApiConfig($restApiConfig);
    }

    /**
     * Retrieves the rest API config.
     *
     * @since [*next-version*]
     *
     * @return array|ArrayAccess|ContainerInterface|stdClass
     */
    protected function _getRestApiConfig()
    {
        return $this->restApiConfig;
    }

    /**
     * Sets the REST API config.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|ContainerInterface|stdClass $restApiConfig The REST API config.
     */
    protected function _setRestApiConfig($restApiConfig)
    {
        $this->restApiConfig = $this->_normalizeContainer($restApiConfig);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _handle(WP_REST_Request $request)
    {
        $response = $this->_getController()->post($request);
        $clients  = $this->_normalizeArray($response);

        if (empty($clients)) {
            return new WP_Error('eddbk_create_client_error', $this->__('A client with that email already exists'), [
                'status' => 409,
            ]);
        }

        $client   = $clients[0];
        $clientId = $client['id'];

        $restApiConfig = $this->_getRestApiConfig();
        $namespace     = $this->_containerGet($restApiConfig, 'namespace');
        $routePattern  = $this->_containerGetPath($restApiConfig, ['routes', 'get_client_info', 'pattern']);
        $routeUrl      = str_replace('(?P<id>[\d]+)', $clientId, $routePattern);

        $clientGetUrl = get_rest_url(null, $namespace . $routeUrl);

        return new WP_REST_Response($client, 201, [
            'Location' => $clientGetUrl,
        ]);
    }
}
