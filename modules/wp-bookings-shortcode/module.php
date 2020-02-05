<?php

use Psr\Container\ContainerInterface;
use \RebelCode\Bookings\WordPress\Module\WpBookingsShortcode;

define('EDDBK_SHORTCODE_MODULE_DIR', __DIR__);
define('EDDBK_SHORTCODE_MODULE_CONFIG', EDDBK_SHORTCODE_MODULE_DIR . '/config.php');
define('EDDBK_SHORTCODE_MODULE_KEY', 'eddbk_shortcode');

return function (ContainerInterface $c) {
    return new WpBookingsShortcode(
        EDDBK_SHORTCODE_MODULE_KEY,
        ['wp_cqrs', 'eddbk_services', 'wp_bookings_front_ui', 'wp_bookings_ui'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
