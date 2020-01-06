<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Cart\Module\EddBkCartModule;

// Module Info
define('EDDBK_CART_MODULE_KEY', 'eddbk_cart');
// Directories
define('EDDBK_CART_MODULE_DIR', __DIR__);
define('EDDBK_CART_MODULE_CONFIG_DIR', EDDBK_CART_MODULE_DIR);
define('EDDBK_CART_MODULE_SERVICES_DIR', EDDBK_CART_MODULE_DIR);
define('EDDBK_CART_MODULE_TEMPLATES_DIR', EDDBK_CART_MODULE_DIR . '/templates');
// Files
define('EDDBK_CART_MODULE_CONFIG_FILE', EDDBK_CART_MODULE_CONFIG_DIR . '/config.php');
define('EDDBK_CART_MODULE_SERVICES_FILE', EDDBK_CART_MODULE_SERVICES_DIR . '/services.php');

return function (ContainerInterface $c) {
    return new EddBkCartModule(
        EDDBK_CART_MODULE_KEY,
        ['wp_bookings_cqrs', 'booking_logic', 'eddbk_services'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
