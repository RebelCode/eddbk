<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\WpBookingsUiModule;

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_KEY', 'wp_bookings_ui');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_SCREEN_OPTIONS_KEY', 'eddbk_screen_options');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_UPDATE_SETTINGS_ACTION', 'update_eddbk_settings');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_DIR', __DIR__);

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_TEMPLATES_DIR', WP_BOOKINGS_UI_MODULE_DIR . DIRECTORY_SEPARATOR . 'templates');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_DEFINITIONS_PATH', __DIR__ . '/services.php');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_RELATIVE_DIR', 'modules/wp-bookings-ui');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_CONFIG_DIR', __DIR__ . '/config');

/* @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_CONFIG_FILE', WP_BOOKINGS_UI_MODULE_CONFIG_DIR . '/config.php');

/**
 * Create and configure module.
 *
 * @since [*next-version*]
 *
 * @param ContainerInterface $c The container.
 *
 * @return WpBookingsUiModule Configured module.
 */
return function (ContainerInterface $c) {
    return new WpBookingsUiModule(
        WP_BOOKINGS_UI_MODULE_KEY,
        ['eddbk_rest_api'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
