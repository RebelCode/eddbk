<?php

use Psr\Container\ContainerInterface;
use \RebelCode\Bookings\WordPress\Module\WpBookingsFrontUi;

define('RC_BOOKINGS_FRONT_UI_APP_VERSION', '0.2.4');
define('RC_BOOKINGS_FRONT_UI_MODULE_RELATIVE_DIR', 'modules/wp-bookings-front-ui');
define('RC_BOOKINGS_FRONT_UI_MODULE_DIR', __DIR__);
define('RC_BOOKINGS_FRONT_UI_TEMPLATES_DIR', RC_BOOKINGS_FRONT_UI_MODULE_DIR . DIRECTORY_SEPARATOR . 'templates');
define('RC_BOOKINGS_FRONT_UI_MODULE_CONFIG_DIR', RC_BOOKINGS_FRONT_UI_MODULE_DIR . DIRECTORY_SEPARATOR . 'config');
define('RC_BOOKINGS_FRONT_UI_MODULE_CONFIG', RC_BOOKINGS_FRONT_UI_MODULE_CONFIG_DIR . DIRECTORY_SEPARATOR . 'config.php');
define('RC_BOOKINGS_FRONT_UI_MODULE_KEY', 'wp_bookings_front_ui');

return function(ContainerInterface $c) {
    return new WpBookingsFrontUi(
        RC_BOOKINGS_FRONT_UI_MODULE_KEY,
        ['wp_bookings_ui', 'eddbk_rest_api'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
