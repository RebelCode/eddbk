<?php

use Dhii\Cache\MemoryMemoizer;
use Dhii\Output\PlaceholderTemplate;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Emails\Module\BookingsEmailTagTemplate;
use RebelCode\EddBookings\Emails\Module\BookingsEmailTag;
use RebelCode\EddBookings\Emails\Module\EmailTagRegisterHandler;

return [
    /*
     * The handler that registers the EDD email tag handlers.
     *
     * @since [*next-version*]
     */
    'eddbk_email_tags_register_handler'        => function (ContainerInterface $c) {
        return new EmailTagRegisterHandler($c->get('eddbk_admin_emails/edd_email_tags'), $c);
    },

    /*
     * The "bookings" EDD email tag handler.
     *
     * @since [*next-version*]
     */
    'eddbk_email_bookings_tag_handler'         => function (ContainerInterface $c) {
        return new BookingsEmailTag(
            $c->get('eddbk_email_bookings_tag_template'),
            $c->get('bookings_select_rm'),
            $c->get('sql_expression_builder')
        );
    },

    /*
     * The template for the "bookings" EDD email tag.
     *
     * @since [*next-version*]
     */
    'eddbk_email_bookings_tag_template'        => function (ContainerInterface $c) {
        return new BookingsEmailTagTemplate(
            $c->get('eddbk_email_bookings_tag_layout_template'),
            $c->get('booking_factory'),
            $c->get('eddbk_services_manager'),
            $c->get('resources_entity_manager'),
            $c->get('eddbk_email_tag_service_cache'),
            $c->get('eddbk_email_tag_resource_cache'),
            $c->get('eddbk_admin_emails/templates/receipt/booking_datetime_format')
        );
    },

    /**
     * The template for the bookings layout.
     *
     * @since [*next-version*]
     */
    'eddbk_email_bookings_tag_layout_template' => function (ContainerInterface $c) {
        $config       = $c->get('eddbk_admin_emails/templates/receipt_layout');
        $templateFile = sprintf('%s/%s', RCMOD_EDDBK_ADMIN_EMAILS_TEMPLATES_DIR, $config->get('file'));
        $template     = file_get_contents($templateFile);

        return new PlaceholderTemplate(
            $template,
            $config->get('token_start'),
            $config->get('token_end'),
            $config->get('token_default')
        );
    },

    /*
     * The cache that caches service by ID.
     *
     * @since [*next-version*]
     */
    'eddbk_email_tag_service_cache' => function (ContainerInterface $c) {
        return new MemoryMemoizer();
    },

    /*
     * The cache that caches resource by ID.
     *
     * @since [*next-version*]
     */
    'eddbk_email_tag_resource_cache' => function (ContainerInterface $c) {
        return new MemoryMemoizer();
    },
];
