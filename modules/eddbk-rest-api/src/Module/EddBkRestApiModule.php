<?php

namespace RebelCode\EddBookings\RestApi\Module;

use ArrayIterator;
use DateTime;
use DateTimeZone;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Factory\GenericCallbackFactory;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use IteratorIterator;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\RestApi\Auth\FilterAuthValidator;
use RebelCode\EddBookings\RestApi\Auth\TolerantCompositeValidator;
use RebelCode\EddBookings\RestApi\Auth\UserIsAdminAuthValidator;
use RebelCode\EddBookings\RestApi\Controller\BookingsController;
use RebelCode\EddBookings\RestApi\Controller\ClientsController;
use RebelCode\EddBookings\RestApi\Controller\ResourcesController;
use RebelCode\EddBookings\RestApi\Controller\ServicesController;
use RebelCode\EddBookings\RestApi\Controller\SessionsController;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\BookingInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\CreateBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\DeleteBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\QueryBookingsHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\UpdateBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\ClientInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\CreateClientHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\QueryClientsHandler;
use RebelCode\EddBookings\RestApi\Handlers\Resources\CreateResourceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Resources\DeleteResourceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Resources\QueryResourcesHandler;
use RebelCode\EddBookings\RestApi\Handlers\Resources\ResourceInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Resources\UpdateResourceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\CreateServiceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\DeleteServiceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\QueryServicesHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\ServiceInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\UpdateServiceHandler;
use RebelCode\EddBookings\RestApi\Handlers\Sessions\QuerySessionsHandler;
use RebelCode\EddBookings\RestApi\Transformer\AvailabilityRuleTransformer;
use RebelCode\EddBookings\RestApi\Transformer\AvailabilityTransformer;
use RebelCode\EddBookings\RestApi\Transformer\BookingTransformer;
use RebelCode\EddBookings\RestApi\Transformer\CoreInfoServiceTransformer;
use RebelCode\EddBookings\RestApi\Transformer\FullInfoServiceTransformer;
use RebelCode\EddBookings\RestApi\Transformer\ResourceIdTransformer;
use RebelCode\EddBookings\RestApi\Transformer\ResourceTransformer;
use RebelCode\EddBookings\RestApi\Transformer\ServiceAvailabilityTransformer;
use RebelCode\EddBookings\RestApi\Transformer\SessionTransformer;
use RebelCode\EddBookings\RestApi\Transformer\SessionTypeDataTransformer;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Transformers\CallbackTransformer;
use RebelCode\Transformers\MapTransformer;
use RebelCode\Transformers\NoOpTransformer;
use RebelCode\Transformers\TransformerIterator;
use RebelCode\WordPress\Nonce\Factory\NonceFactory;
use RebelCode\WordPress\Nonce\Factory\NonceFactoryInterface;
use RebelCode\WordPress\Nonce\NonceInterface;
use Traversable;
use WP_Post;

/**
 * The REST API module class.
 *
 * @since [*next-version*]
 */
class EddBkRestApiModule extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use NormalizeIteratorCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If an error occurred while trying to read from the config file.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RCMOD_EDDBK_REST_API_CONFIG_FILE),
            [
                /*-------------------------------------------------------------*\
                 * Resource Controllers                                        *
                \*-------------------------------------------------------------*/

                /*
                 * The bookings REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_bookings_controller' => function (ContainerInterface $c) {
                    return new BookingsController(
                        $c->get('eddbk_rest_api_bookings_iterator_factory'),
                        $c->get('booking_factory'),
                        $c->get('booking_transitioner'),
                        $c->get('bookings_select_rm'),
                        $c->get('bookings_entity_manager'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_clients_controller')
                    );
                },

                /*
                 * The resources REST API controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_resources_controller' => function (ContainerInterface $c) {
                    return new ResourcesController(
                        $c->get('eddbk_rest_api_resources_iterator_factory'),
                        $c->get('resources_entity_manager'),
                        $c->get('event_manager'),
                        $c->get('event_factory')
                    );
                },

                /*
                 * The services REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_services_controller' => function (ContainerInterface $c) {
                    return new ServicesController(
                        $c->get('eddbk_services_manager'),
                        $c->get('eddbk_rest_api_services_iterator_factory'),
                        $c->get('eddbk_rest_api_user_is_admin_auth_validator'),
                        $c->get('eddbk_rest_api_user_is_admin_auth_validator'),
                        $c->get('event_factory')
                    );
                },

                /*
                 * The clients REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_clients_controller' => function (ContainerInterface $c) {
                    return new ClientsController($c->get('eddbk_rest_api_clients_iterator_factory'), EDD()->customers);
                },

                /*
                 * The sessions REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_sessions_controller' => function (ContainerInterface $c) {
                    return new SessionsController(
                        $c->get('eddbk_rest_api_sessions_iterator_factory'),
                        $c->get('unbooked_sessions_select_rm'),
                        $c->get('eddbk_rest_api_sessions_ordering'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_rest_api/controllers/sessions/default_num_sessions_per_page'),
                        $c->get('eddbk_rest_api/controllers/sessions/max_num_sessions_per_page')
                    );
                },

                /**
                 * The order to use for sessions in the REST API.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_ordering' => function (ContainerInterface $c) {
                    return [
                        $c->get('sql_order_factory')->make([
                            'entity'    => 'session',
                            'field'     => 'start',
                            'ascending' => true,
                        ]),
                    ];
                },

                /*-------------------------------------------------------------*\
                 * Controller Result Iterator Factories                        *
                \*-------------------------------------------------------------*/

                /*
                 * The iterator factory for the bookings controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_bookings_transformer'));
                    });
                },

                /*
                 * The iterator factory for the resources controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_resources_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items    = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_resource_transformer'));
                    });
                },

                /*
                 * The iterator factory for the services controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_services_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items    = $this->_containerGet($config, 'items');
                        $coreOnly = $this->_containerGet($config, 'core_only');

                        // Iterator of results
                        $iterator = $this->_normalizeIterator($items);
                        // Transformer to apply to each
                        $transformer = ($coreOnly)
                            ? $c->get('eddbk_rest_api_core_info_service_transformer')
                            : $c->get('eddbk_rest_api_full_info_service_transformer');

                        return new TransformerIterator($iterator, $transformer);
                    });
                },

                /*
                 * The iterator factory for the clients controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_clients_transformer'));
                    });
                },

                /*
                 * The iterator factory for the session controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_sessions_transformer'));
                    });
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Bookings                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the bookings route that receives generic booking queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_bookings_handler' => function (ContainerInterface $c) {
                    return new QueryBookingsHandler(
                        $c->get('eddbk_bookings_controller'),
                        $c->get('booking_status_select_rm'),
                        $c->get('booking_logic/statuses')
                    );
                },

                /*
                 * Handles the bookings route that provides information about a single booking.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_booking_info_handler' => function (ContainerInterface $c) {
                    return new BookingInfoHandler($c->get('eddbk_bookings_controller'));
                },

                /*
                 * Handles the bookings route for creating new bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_booking_handler' => function (ContainerInterface $c) {
                    return new CreateBookingHandler($c->get('eddbk_bookings_controller'), $c->get('eddbk_rest_api'));
                },

                /*
                 * Handles the bookings route for updating bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_update_booking_handler' => function (ContainerInterface $c) {
                    return new UpdateBookingHandler($c->get('eddbk_bookings_controller'));
                },

                /*
                 * Handles the bookings route for deleting bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_delete_booking_handler' => function (ContainerInterface $c) {
                    return new DeleteBookingHandler($c->get('eddbk_bookings_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Resources                         *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the resources route that receives generic resource queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_resources_handler' => function (ContainerInterface $c) {
                    return new QueryResourcesHandler($c->get('eddbk_resources_controller'));
                },

                /*
                 * Handles the resources route that provides information about a single resource.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_resource_info_handler' => function (ContainerInterface $c) {
                    return new ResourceInfoHandler($c->get('eddbk_resources_controller'));
                },

                /*
                 * Handles the resources route for creating new resources.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_resource_handler' => function (ContainerInterface $c) {
                    return new CreateResourceHandler($c->get('eddbk_resources_controller'), $c->get('eddbk_rest_api'));
                },

                /*
                 * Handles the resources route for updating resources.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_update_resource_handler' => function (ContainerInterface $c) {
                    return new UpdateResourceHandler($c->get('eddbk_resources_controller'));
                },

                /*
                 * Handles the resources route for deleting resources.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_delete_resource_handler' => function (ContainerInterface $c) {
                    return new DeleteResourceHandler($c->get('eddbk_resources_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Clients                           *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the clients route that receives generic client queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_clients_handler' => function (ContainerInterface $c) {
                    return new QueryClientsHandler($c->get('eddbk_clients_controller'));
                },

                /*
                 * Handles the clients route that provides information about a single client.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_client_info_handler' => function (ContainerInterface $c) {
                    return new ClientInfoHandler($c->get('eddbk_clients_controller'));
                },

                /*
                 * Handles the clients route for creating new clients.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_client_handler' => function (ContainerInterface $c) {
                    return new CreateClientHandler($c->get('eddbk_clients_controller'), $c->get('eddbk_rest_api'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Sessions                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the sessions route that receives generic session queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_sessions_handler' => function (ContainerInterface $c) {
                    return new QuerySessionsHandler($c->get('eddbk_sessions_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Services                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the services route that receives generic service queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_services_handler' => function (ContainerInterface $c) {
                    return new QueryServicesHandler($c->get('eddbk_services_controller'));
                },

                /*
                 * Handles the services route that provides information about a single service.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_service_info_handler' => function (ContainerInterface $c) {
                    return new ServiceInfoHandler($c->get('eddbk_services_controller'));
                },

                /*
                 * Handles the services route for creating new services.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_service_handler' => function (ContainerInterface $c) {
                    return new CreateServiceHandler($c->get('eddbk_services_controller'), $c->get('eddbk_rest_api'));
                },

                /*
                 * Handles the services route for updating services.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_update_service_handler' => function (ContainerInterface $c) {
                    return new UpdateServiceHandler($c->get('eddbk_services_controller'));
                },

                /*
                 * Handles the services route for deleting services.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_delete_service_handler' => function (ContainerInterface $c) {
                    return new DeleteServiceHandler($c->get('eddbk_services_controller'));
                },

                /*-------------------------------------------------------------*\
                 * Transformers - for data, not robots in disguise             *
                \*-------------------------------------------------------------*/

                /*
                 * A no-operation transformer.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_noop_transformer' => function (ContainerInterface $c = null) {
                    return new NoOpTransformer();
                },

                /*
                 * The transformer that transforms bookings into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_transformer' => function (ContainerInterface $c) {
                    return new BookingTransformer(
                        $c->get('eddbk_timestamp_datetime_transformer'),
                        $c->get('eddbk_rest_api_service_id_transformer'),
                        $c->get('eddbk_rest_api_bookings_resource_id_transformer'),
                        $c->get('eddbk_rest_api_client_id_transformer')
                    );
                },

                /*
                 * The transformer used by the bookings transformer to change resource IDs into resource data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_resource_id_transformer' => function (ContainerInterface $c) {
                    return new ResourceIdTransformer(
                        $c->get('eddbk_rest_api_simple_resource_transformer'),
                        $c->get('resources_entity_manager')
                    );
                },

                /*
                 * The transformer that transforms resources into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_resource_transformer' => function (ContainerInterface $c) {
                    return new ResourceTransformer($c->get('eddbk_rest_api_availability_transformer'));
                },

                /*
                 * The transformer that partially transforms resources into the result that is sent in REST API
                 * responses. This excludes resource availability from the result.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_simple_resource_transformer' => function (ContainerInterface $c) {
                    return new ResourceTransformer();
                },

                /*
                 * The availability transformer for transforming availabilities.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_availability_transformer' => function (ContainerInterface $c) {
                    return new AvailabilityTransformer(
                        $c->get('eddbk_rest_api_availability_rule_transformer')
                    );
                },

                /*
                 * The transformer that transforms services into the results that only contain core info.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_core_info_service_transformer' => function (ContainerInterface $c) {
                    return new CoreInfoServiceTransformer(
                        $c->get('eddbk_rest_api_service_session_type_list_transformer')
                    );
                },

                /*
                 * The transformer that transforms services into the results that contain full info.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_full_info_service_transformer' => function (ContainerInterface $c) {
                    return new FullInfoServiceTransformer(
                        $c->get('eddbk_rest_api_service_session_type_list_transformer'),
                        $c->get('eddbk_rest_api_service_availability_transformer'),
                        $c->get('eddbk_boolean_transformer')
                    );
                },

                /*
                 * The transformer that transformers service availabilities into the correct response format.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_availability_transformer' => function (ContainerInterface $c) {
                    return new ServiceAvailabilityTransformer(
                        $c->get('eddbk_rest_api_availability_rule_transformer')
                    );
                },

                /*
                 * The transformer that transformers service availability rules into the correct response format.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_availability_rule_transformer' => function (ContainerInterface $c) {
                    return new AvailabilityRuleTransformer(
                        $c->get('eddbk_timestamp_datetime_transformer'),
                        $c->get('eddbk_boolean_transformer'),
                        $c->get('eddbk_comma_list_array_transformer'),
                        $c->get('eddbk_rest_api_service_exclude_dates_transformer')
                    );
                },

                /*
                 * The transformer for transforming a list of session type configs.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_session_type_list_transformer'    => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($sessionLengths) use ($c) {
                        $iterator    = $this->_normalizeIterator($sessionLengths);
                        $transformer = $c->get('eddbk_rest_api_session_type_transformer');
                        $result      = new TransformerIterator($iterator, $transformer);

                        return iterator_to_array($result);
                    });
                },

                /*
                 * The transformer for transforming a session type config.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_session_type_transformer'              => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            MapTransformer::K_SOURCE => 'id',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'type',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'label',
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'price',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_rest_api_session_type_price_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'data',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_rest_api_session_type_data_transformer')
                        ],
                    ]);
                },

                /*
                 * The transformer that transforms session type data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_session_type_data_transformer' => function (ContainerInterface $c) {
                    return new SessionTypeDataTransformer(
                        $c->get('eddbk_rest_api_resource_id_transformer')
                    );
                },

                /*
                 * The transformer that transforms resource IDs into full resource data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_resource_id_transformer' => function (ContainerInterface $c) {
                    return new ResourceIdTransformer(
                        $c->get('eddbk_rest_api_resource_transformer'),
                        $c->get('resources_entity_manager')
                    );
                },

                /*
                 * The transformer for transforming a session type's price.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_session_type_price_transformer'     => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($price) use ($c) {
                        return [
                            'amount'    => $price,
                            'currency'  => \edd_get_currency(),
                            'formatted' => $c->get('eddbk_rest_api_price_transformer')->transform($price),
                        ];
                    });
                },

                /*
                 * The transformer for transforming a price amount into its formatted counterpart.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_price_transformer'                          => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($price) use ($c) {
                        return html_entity_decode(\edd_currency_filter(\edd_format_amount($price)));
                    });
                },

                /*
                 * The transformer for transforming the session rule excluded dates for services.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_exclude_dates_transformer'       => function (ContainerInterface $c) {
                    $commaListTransformer = $c->get('eddbk_comma_list_array_transformer');
                    $datetimeTransformer  = $c->get('eddbk_timestamp_datetime_transformer');

                    return new CallbackTransformer(function ($value) use ($commaListTransformer, $datetimeTransformer) {
                        // Transform comma list to an iterator
                        $array    = $commaListTransformer->transform($value);
                        $iterator = $this->_normalizeIterator($array);
                        // Create the transformer iterator, to transform each timestamp into a datetime string
                        $transformIterator = new TransformerIterator($iterator, $datetimeTransformer);

                        // Reduce to an array and return
                        return iterator_to_array($transformIterator);
                    });
                },

                /*
                 * The transformer that transforms clients into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_transformer' => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            MapTransformer::K_SOURCE => 'id',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'name',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'email',
                        ],

                    ]);
                },

                /*
                 * The transformer that transformers sessions into the result that is sent in the REST API response.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_transformer' => function (ContainerInterface $c) {
                    return new SessionTransformer($c->get('eddbk_timestamp_datetime_transformer'));
                },

                /*
                 * The transformer that transforms a service ID into service data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_id_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($serviceId) use ($c) {
                        if (empty($serviceId)) {
                            return;
                        }

                        $controller = $c->get('eddbk_services_controller');
                        $services = $controller->get(['id' => $serviceId]);
                        $service = null;

                        foreach ($services as $service) {
                            break;
                        }

                        return $service;
                    });
                },

                /*
                 * The transformer that transforms a client ID into client data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_client_id_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($clientId) use ($c) {
                        if (empty($clientId)) {
                            return;
                        }

                        $controller = $c->get('eddbk_clients_controller');
                        $clients = $controller->get(['id' => $clientId]);
                        $client = null;

                        foreach ($clients as $client) {
                            break;
                        }

                        return $client;
                    });
                },

                /*
                 * Transformer that transforms timestamps into formatted datetime strings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_timestamp_datetime_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($timestamp) use ($c) {
                        // Get WordPress timezone, defaulting to UTC
                        $tzName = get_option('timezone_string');
                        $tzName = empty($tzName) ? 'UTC' : $tzName;
                        // Create date time object with the timezone
                        $timezone = new DateTimeZone($tzName);
                        $dateTime = new DateTime('@' . $timestamp, $timezone);

                        // Return formatted
                        return $dateTime->format($c->get('eddbk_rest_api/datetime_format'));
                    });
                },

                /*
                 * Transformer that transforms WP_Post instances into arrays.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_post_array_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($post) {
                        if (empty($post)) {
                            return;
                        }

                        if (!($post instanceof WP_Post)) {
                            throw $this->_createInvalidArgumentException(
                                $this->__('Argument is not a WP_Post instance'), null, null, $post
                            );
                        }

                        return $post->to_array();
                    });
                },

                /*
                 * The transformer for transforming values into booleans.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_boolean_transformer'                        => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($value) {
                        return (bool) $value;
                    });
                },

                /*
                 * The transformer for transforming comma separating strings into arrays.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_comma_list_array_transformer'               => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($commaList) {
                        return (strlen($commaList) > 0)
                            ? explode(',', $commaList)
                            : [];
                    });
                },

                /*
                 * The transformer for transforming un-serializing strings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_unserialize_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($string) {
                        return unserialize($string);
                    });
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Auth Validators                   *
                \*-------------------------------------------------------------*/

                /*
                 * The authorization validator that only authorizes administrator users.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_user_is_admin_auth_validator' => function (ContainerInterface $c) {
                    return new UserIsAdminAuthValidator($c->get('eddbk_rest_api/admin_capability'));
                },

                /*
                 * The authorization validator that authorizes client apps via filter event.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_filter_event_auth_validator' => function (ContainerInterface $c) {
                    return new FilterAuthValidator(
                        $c->get('event_manager'),
                        $c->get('event_factory'),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/event_name'),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/event_param_key'),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/event_param_default')
                    );
                },

                /*
                 * The authorization validator that authorizes WordPress client apps.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_wp_client_app_auth_validator' => function (ContainerInterface $c) {
                    return new TolerantCompositeValidator([
                        $c->get('eddbk_rest_api_user_is_admin_auth_validator'),
                        $c->get('eddbk_rest_api_filter_event_auth_validator')
                    ]);
                },

                /*-------------------------------------------------------------*\
                 * Misc. REST API services                                     *
                \*-------------------------------------------------------------*/

                /**
                 * The factory that creates WordPress nonce instances for the REST API.
                 *
                 * @since [*next-version*]
                 *
                 * @return NonceFactoryInterface
                 */
                'eddbk_rest_api_nonce_factory' => function (ContainerInterface $c) {
                    return new NonceFactory();
                },

                /**
                 * The factory that creates the WordPress client app auth nonce instance.
                 *
                 * @since [*next-version*]
                 *
                 * @return NonceFactoryInterface
                 */
                'eddbk_rest_api_wp_client_app_nonce_factory' => function (ContainerInterface $c) {
                    return new TransientNonceFactory(
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/transient_name'),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/transient_expiry')
                    );
                },

                /**
                 * The nonce used to authorize WordPress client apps.
                 *
                 * @return NonceInterface
                 */
                'eddbk_rest_api_wp_client_app_nonce' => function (ContainerInterface $c) {
                    $factory = $c->get('eddbk_rest_api_wp_client_app_nonce_factory');
                    $nonceId = $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/handler/nonce');

                    return $factory->make([
                        NonceFactoryInterface::K_CONFIG_ID => $nonceId
                    ]);
                },

                /*
                 * The handler that checks and verifies a nonce to authorize WordPress client apps.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_wp_client_app_auth_nonce_handler' => function (ContainerInterface $c) {
                    /* @var $nonce NonceInterface */
                    $nonce = $c->get('eddbk_rest_api_wp_client_app_nonce');

                    return new TransientNonceAuthHandler(
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/handler/header'),
                        $nonce->getId(),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/event_param_key'),
                        $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/transient_name')
                    );
                },

                /*
                 * The REST API initializer - initializes the routes and handlers when invoked.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_initializer' => function (ContainerInterface $c) {
                    return new RestApiInitializer($c->get('eddbk_rest_api'), $c);
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        if ($c === null) {
            return;
        }

        $this->_attach('rest_api_init', $c->get('eddbk_rest_api_initializer'));

        // Attach handler for WP client apps to be authorized by nonce
        $this->_attach(
            $c->get('eddbk_rest_api/auth/transient_nonce_filter_validator/event_name'),
            $c->get('eddbk_rest_api_wp_client_app_auth_nonce_handler')
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }
}
