<?php

use Psr\Container\ContainerInterface;
use \RebelCode\Bookings\WordPress\Module\WpBookingsShortcode;

define('RC_WP_BOOKINGS_SHORTCODE_MODULE_DIR', __DIR__);
define('RC_WP_BOOKINGS_SHORTCODE_MODULE_CONFIG', RC_WP_BOOKINGS_SHORTCODE_MODULE_DIR . '/config.php');
define('RC_WP_BOOKINGS_SHORTCODE_MODULE_KEY', 'wp_bookings_shortcode');

return function (ContainerInterface $c) {
    return new WpBookingsShortcode(
        RC_WP_BOOKINGS_SHORTCODE_MODULE_KEY,
        ['wp_cqrs', 'eddbk_services', 'wp_bookings_front_ui', 'wp_bookings_ui'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
