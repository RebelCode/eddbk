<?php

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Services\Module\AdminDeleteServiceHandler;
use RebelCode\EddBookings\Services\Module\GetServiceHasPriceOptionsHandler;
use RebelCode\EddBookings\Services\Module\GetServicePriceHandler;
use RebelCode\EddBookings\Services\Module\GetServicePriceOptionsHandler;
use RebelCode\EddBookings\Services\Module\HideServicesFromDownloadsHandler;
use RebelCode\EddBookings\Services\Module\SessionTypesMigrationHandler;
use RebelCode\EddBookings\Services\Storage\ServicesEntityManager;
use RebelCode\Transformers\CallbackTransformer;

/**
 * The service list for the EDD Bookings Services module.
 *
 * @since [*next-version*]
 */
class EddBkServicesServiceList extends ArrayObject
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIteratorCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
        parent::__construct($this->_getServices());
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

    /**
     * Retrieves the services.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getServices()
    {
        return [
            /**
             * The services manager.
             *
             * @since [*next-version*]
             */
            'eddbk_services_manager'                           => function (ContainerInterface $c) {
                return new ServicesEntityManager(
                    $c->get('eddbk_services/post_type'),
                    $c->get('eddbk_services/meta_prefix'),
                    $c->get('resources_entity_manager'),
                    $c->get('session_rules_select_rm'),
                    $c->get('session_rules_insert_rm'),
                    $c->get('session_rules_update_rm'),
                    $c->get('session_rules_delete_rm'),
                    $c->get('sql_expression_builder')
                );
            },

            /*
             * The handler for handling service deletion.
             *
             * @since [*next-version*]
             */
            'eddbk_admin_delete_service_handler'               => function (ContainerInterface $c) {
                return new AdminDeleteServiceHandler(
                    $c->get('eddbk_services/post_type'),
                    $c->get('eddbk_services/meta_prefix'),
                    $c->get('resources_delete_rm'),
                    $c->get('sessions_delete_rm'),
                    $c->get('session_rules_delete_rm'),
                    $c->get('sql_expression_builder')
                );
            },

            /*
             * The transformer for transforming a price amount into its formatted counterpart.
             *
             * @since [*next-version*]
             */
            'eddbk_price_transformer'                          => function (ContainerInterface $c) {
                return new CallbackTransformer(function ($price) use ($c) {
                    return html_entity_decode(edd_currency_filter(edd_format_amount($price)));
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
             * The handler that changes the Download price to the smallest session length's price.
             *
             * @since [*next-version*]
             */
            'eddbk_get_service_price_handler'                  => function (ContainerInterface $c) {
                return new GetServicePriceHandler(
                    $c->get('eddbk_services_manager')
                );
            },

            /*
             * The handler that changes the Download price options to the session length prices.
             *
             * @since [*next-version*]
             */
            'eddbk_get_service_price_options_handler'          => function (ContainerInterface $c) {
                return new GetServicePriceOptionsHandler(
                    $c->get('eddbk_services_manager')
                );
            },

            /*
             * The handler that filters the Download price options flag.
             *
             * @since [*next-version*]
             */
            'eddbk_get_service_has_price_options_handler'      => function (ContainerInterface $c) {
                return new GetServiceHasPriceOptionsHandler(
                    $c->get('eddbk_services_manager')
                );
            },

            /**
             * The handler that hides services from the downloads list.
             *
             * @since [*next-version*]
             */
            'eddbk_hide_services_from_downloads_list_handler' => function (ContainerInterface $c) {
                return new HideServicesFromDownloadsHandler(
                    $c->get('eddbk_services/post_type'),
                    $c->get('eddbk_services_manager')
                );
            },

            /*
             * The migration handler updating service session types when the database is upgraded to version 3.
             *
             * @since [*next-version*]
             */
            'eddbk_services_session_types_migration_3_handler' => function (ContainerInterface $c) {
                return new SessionTypesMigrationHandler(
                    $c->get('wpdb'),
                    $c->get('eddbk_services/post_type'),
                    $c->get('eddbk_services/meta_prefix')
                );
            }
        ];
    }
}

return new EddBkServicesServiceList();
