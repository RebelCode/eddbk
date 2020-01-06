<?php

use Dhii\Cache\MemoryMemoizer;
use Dhii\Output\PlaceholderTemplateFactory;
use Dhii\Output\TemplateFactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Cart\BookingPriceEvaluator;
use RebelCode\EddBookings\Cart\BookingValueAwareFactory;
use RebelCode\EddBookings\Cart\Module\AddBookingToCartHandler;
use RebelCode\EddBookings\Cart\Module\FilterCartItemNameHandler;
use RebelCode\EddBookings\Cart\Module\FilterCartItemPriceHandler;
use RebelCode\EddBookings\Cart\Module\RemoveBookingFromCartHandler;
use RebelCode\EddBookings\Cart\Module\RenderCartBookingInfoHandler;
use RebelCode\EddBookings\Cart\Module\RenderConfirmationBookingsHandler;
use RebelCode\EddBookings\Cart\Module\SubmitBookingOnPaymentHandler;
use RebelCode\EddBookings\Cart\Module\ValidateCartBookingHandler;

return [
    /*
     * The EDD cart instance.
     *
     * @since [*next-version*]
     */
    'edd_cart' => function (ContainerInterface $c) {
        return EDD()->cart;
    },

    /*
     * The handler that adds bookings to the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_add_booking_to_cart_handler' => function (ContainerInterface $c) {
        return new AddBookingToCartHandler(
            $c->get('eddbk_services_manager'),
            $c->get('edd_cart'),
            $c->get('eddbk_cart/cart_items')
        );
    },

    /*
     * The handler that deletes bookings when they are removed from the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_remove_booking_from_cart_handler' => function (ContainerInterface $c) {
        return new RemoveBookingFromCartHandler(
            $c->get('edd_cart'),
            $c->get('bookings_select_rm'),
            $c->get('bookings_delete_rm'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/cart_items')
        );
    },

    /*
     * The handler that deletes bookings when they are removed from the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_submit_booking_on_payment_handler' => function (ContainerInterface $c) {
        return new SubmitBookingOnPaymentHandler(
            $c->get('booking_transitioner'),
            $c->get('booking_factory'),
            $c->get('bookings_select_rm'),
            $c->get('bookings_update_rm'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/cart_items')
        );
    },

    /*
     * The handler that validates bookings in the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_validate_cart_bookings_handler' => function (ContainerInterface $c) {
        return new ValidateCartBookingHandler(
            $c->get('edd_cart'),
            $c->get('booking_validator'),
            $c->get('booking_factory'),
            $c->get('bookings_select_rm'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/cart_items')
        );
    },

    /*
     * The handler that filters cart item names.
     *
     * @since [*next-version*]
     */
    'eddbk_filter_cart_item_name_handler' => function (ContainerInterface $c) {
        return new FilterCartItemNameHandler(
            $c->get('eddbk_services_manager')
        );
    },

    /*
     * The handler that filters cart item prices.
     *
     * @since [*next-version*]
     */
    'eddbk_filter_cart_item_price_handler' => function (ContainerInterface $c) {
        return new FilterCartItemPriceHandler(
            $c->get('bookings_select_rm'),
            $c->get('booking_factory'),
            $c->get('eddbk_booking_price_evaluator'),
            $c->get('eddbk_booking_value_aware_factory'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/cart_items')
        );
    },

    /*
     * The handler that renders booking information in the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_render_cart_booking_info_handler' => function (ContainerInterface $c) {
        return new RenderCartBookingInfoHandler(
            $c->get('eddbk_cart_booking_info_template'),
            $c->get('bookings_select_rm'),
            $c->get('booking_factory'),
            $c->get('eddbk_services_manager'),
            $c->get('resources_entity_manager'),
            $c->get('eddbk_cart_service_cache'),
            $c->get('eddbk_cart_resource_cache'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/cart_items'),
            $c->get('eddbk_cart/fallback_timezone')
        );
    },

    /*
     * The handler that renders the bookings in the EDD purchase confirmation page.
     *
     * @since [*next-version*]
     */
    'eddbk_render_confirmation_bookings_handler' => function(ContainerInterface $c) {
        return new RenderConfirmationBookingsHandler(
            $c->get('eddbk_confirmation_table_template'),
            $c->get('eddbk_confirmation_booking_row_template'),
            $c->get('bookings_select_rm'),
            $c->get('booking_factory'),
            $c->get('eddbk_services_manager'),
            $c->get('resources_entity_manager'),
            $c->get('eddbk_cart_service_cache'),
            $c->get('eddbk_cart_resource_cache'),
            $c->get('sql_expression_builder'),
            $c->get('eddbk_cart/confirmation_page/booking_datetime_format'),
            $c->get('eddbk_cart/fallback_timezone')
        );
    },

    /*
     * The cache that caches service by ID.
     *
     * @since [*next-version*]
     */
    'eddbk_cart_service_cache' => function (ContainerInterface $c) {
        return new MemoryMemoizer();
    },

    /*
     * The cache that caches resource by ID.
     *
     * @since [*next-version*]
     */
    'eddbk_cart_resource_cache' => function (ContainerInterface $c) {
        return new MemoryMemoizer();
    },

    /*
     * The factory used to create templates used in this module.
     *
     * @since [*next-version*]
     */
    'eddbk_cart_template_factory' => function (ContainerInterface $c) {
        return new PlaceholderTemplateFactory(
            'Dhii\Output\PlaceholderTemplate',
            $c->get('eddbk_cart/templates/token_start'),
            $c->get('eddbk_cart/templates/token_end'),
            $c->get('eddbk_cart/templates/token_default')
        );
    },

    /*
     * The template for booking info in the EDD cart.
     *
     * @since [*next-version*]
     */
    'eddbk_cart_booking_info_template' => function (ContainerInterface $c) {
        $templateFile = $c->get('eddbk_cart/cart_items/templates/booking_info');
        $templatePath = EDDBK_CART_MODULE_TEMPLATES_DIR . DIRECTORY_SEPARATOR . $templateFile;
        $template = file_get_contents($templatePath);

        return $c->get('eddbk_cart_template_factory')->make([
            TemplateFactoryInterface::K_TEMPLATE => $template
        ]);
    },

    /*
     * The template for the bookings table in the purchase confirmation page.
     *
     * @since [*next-version*]
     */
    'eddbk_confirmation_table_template' => function(ContainerInterface $c) {
        $templateFile = $c->get('eddbk_cart/confirmation_page/templates/table');
        $templatePath = EDDBK_CART_MODULE_TEMPLATES_DIR . DIRECTORY_SEPARATOR . $templateFile;
        $template = file_get_contents($templatePath);

        return $c->get('eddbk_cart_template_factory')->make([
            TemplateFactoryInterface::K_TEMPLATE => $template
        ]);
    },

    /*
     * The template for the bookings rows in the purchase confirmation page bookings table.
     *
     * @since [*next-version*]
     */
    'eddbk_confirmation_booking_row_template' => function(ContainerInterface $c) {
        $templateFile = $c->get('eddbk_cart/confirmation_page/templates/booking_row');
        $templatePath = EDDBK_CART_MODULE_TEMPLATES_DIR . DIRECTORY_SEPARATOR . $templateFile;
        $template = file_get_contents($templatePath);

        return $c->get('eddbk_cart_template_factory')->make([
            TemplateFactoryInterface::K_TEMPLATE => $template
        ]);
    },

    /*
     * The booking price evaluator.
     *
     * @since [*next-version*]
     */
    'eddbk_booking_price_evaluator' => function (ContainerInterface $c) {
        return new BookingPriceEvaluator(
            $c->get('eddbk_services_manager')
        );
    },

    /*
     * The factory that creates booking value-aware instances.
     *
     * @since [*next-version*]
     */
    'eddbk_booking_value_aware_factory' => function (ContainerInterface $c) {
        return new BookingValueAwareFactory();
    },
];
