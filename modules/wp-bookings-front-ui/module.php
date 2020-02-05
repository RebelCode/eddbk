<?php

use Psr\Container\ContainerInterface;
use \RebelCode\Bookings\WordPress\Module\WpBookingsFrontUi;

define('EDDBK_FRONT_UI_APP_VERSION', '0.2.4');
define('EDDBK_FRONT_UI_MODULE_RELATIVE_DIR', 'modules/wp-bookings-front-ui');
define('EDDBK_FRONT_UI_MODULE_DIR', __DIR__);
define('EDDBK_FRONT_UI_TEMPLATES_DIR', EDDBK_FRONT_UI_MODULE_DIR . '/templates');
define('EDDBK_FRONT_UI_MODULE_CONFIG_DIR', EDDBK_FRONT_UI_MODULE_DIR . '/config');
define('EDDBK_FRONT_UI_MODULE_CONFIG', EDDBK_FRONT_UI_MODULE_CONFIG_DIR . '/config.php');
define('EDDBK_FRONT_UI_MODULE_KEY', 'wp_bookings_front_ui');

return function(ContainerInterface $c) {
    return new WpBookingsFrontUi(
        EDDBK_FRONT_UI_MODULE_KEY,
        ['wp_bookings_ui', 'eddbk_rest_api'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
