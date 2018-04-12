<?php

/*
 * @wordpress-plugin
 *
 * Plugin Name: Easy Digital Downloads - Bookings
 * Plugin URI: https://eddbookings.com
 * Description: Adds a customizable booking system to Easy Digital Downloads.
 * Version: 0.0.0-dev
 * Author: RebelCode
 * Text Domain: eddbk
 * Domain Path: /languages/
 * License: GPLv3
 */

/*
 * Copyright (C) 2015-2018 RebelCode Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Dhii\Modular\Module\ModuleInterface;
use RebelCode\EddBookings\Core\Di\CompositeContainerFactory;
use RebelCode\EddBookings\Core\Di\ContainerFactory;
use RebelCode\EddBookings\Core\ExceptionHandler;
use RebelCode\EddBookings\Core\PluginModule;
use RebelCode\Modular\Finder\ModuleFileFinder;

// Plugin info
define('EDDBK_SLUG', 'eddbk');
define('EDDBK_VERSION', '0.0.0-dev');
define('EDDBK_MIN_PHP_VERSION', '5.4.0');
// Paths
define('EDDBK_FILE', __FILE__);
define('EDDBK_DIR', __DIR__);
define('EDDBK_SRC_DIR', EDDBK_DIR . DIRECTORY_SEPARATOR . 'src');
define('EDDBK_VENDOR_DIR', EDDBK_DIR . DIRECTORY_SEPARATOR . 'vendor');
define('EDDBK_MODULES_DIR', EDDBK_DIR . DIRECTORY_SEPARATOR . 'modules');
define('EDDBK_AUTOLOAD_FILE', EDDBK_VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php');
// I18n
define('EDDBK_TEXT_DOMAIN', 'eddbk');
// Misc
define('EDDBK_CONTACT_PAGE_URL', 'http://eddbookings.com/contact');

// Deactivate plugin on unhandled exception? Can be defined in `wp-config.php`
if (!defined('EDDBK_SAFE_EXCEPTION_HANDLING')) {
    define('EDDBK_SAFE_EXCEPTION_HANDLING', true);
}

// Check PHP version before continuing
if (version_compare(PHP_VERSION, EDDBK_MIN_PHP_VERSION) < 0) {
    $message = __('EDD Bookings requires PHP %s', EDDBK_TEXT_DOMAIN);
    $message = sprintf($message, EDDBK_MIN_PHP_VERSION);
    $exception = new RuntimeException($message);

    eddBkHandleException($exception);
}

// Ensure modules directory exists
if (!file_exists(EDDBK_MODULES_DIR)) {
    mkdir(EDDBK_MODULES_DIR);
}

// Load autoload file if it exists
if (file_exists(EDDBK_AUTOLOAD_FILE)) {
    require EDDBK_AUTOLOAD_FILE;
}

// Run the core plugin module
runEddBkCore();

/**
 * Retrieves the plugin core module, creating it if necessary.
 *
 * @since [*next-version*]
 *
 * @return PluginModule The core plugin module instance.
 */
function getEddBkCore()
{
    static $instance = null;

    if ($instance === null) {
        /*
         * Initialize module file finder
         * This is just a special traversable, and as such has no effect until the instance is iterated over.
         * Filter handlers can append to this using {@see AppendIterator}, for example.
         */
        $fileFinder = new ModuleFileFinder(EDDBK_MODULES_DIR);
        $fileFinder = apply_filters('eddbk_core_module_file_finder', $fileFinder);

        /*
         * The factory for creating containers.
         * Used by the plugin's modular system, as well as by modules.
         */
        $containerFactory = new ContainerFactory();
        $containerFactory = apply_filters('eddbk_core_module_container_factory', $containerFactory);

        /*
         * The factory for creating the plugin's composite container.
         * This container will hold other containers as children - for instance the containers given by child modules.
         */
        $compContainerFactory = new CompositeContainerFactory();
        $compContainerFactory = apply_filters('eddbk_core_module_composite_container_factory', $compContainerFactory);

        /*
         * The core plugin module.
         * This is a special module that loads other modules.
         */
        $coreModule = new PluginModule(EDDBK_SLUG, $containerFactory, $compContainerFactory, $fileFinder);
        $coreModule = apply_filters('eddbk_core_module', $coreModule);

        // Safety check - in case a filter did something wonky
        if (!$coreModule instanceof ModuleInterface) {
            wp_die(__('Core module is not a module instance.', EDDBK_TEXT_DOMAIN));
        }

        $instance = $coreModule;
    }

    return $instance;
}

/**
 * Invokes the EDD Bookings core module.
 *
 * @since [*next-version*]
 */
function runEddBkCore()
{
    getEddBkErrorHandler()->register();

    // Set up core module
    $container = getEddBkCore()->setup();

    // Run core module when all plugins have been loaded
    add_filter(
        'plugins_loaded',
        function() use ($container) {

            getEddBkCore()->run($container);
        },
        0
    );
}

/**
 * Retrieves the error handler for this plugin.
 *
 * @since [*next-version*]
 *
 * @return ExceptionHandler The error handler.
 */
function getEddBkErrorHandler()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new ExceptionHandler(EDDBK_DIR, 'eddBkHandleException');
    }

    return $instance;
}

/**
 * Handles an exception.
 *
 * @since [*next-version*]
 *
 * @param Exception|Throwable $exception The exception.
 */
function eddBkHandleException($exception)
{
    if (EDDBK_SAFE_EXCEPTION_HANDLING) {
        eddBkDeactivateSelf();
    }

    eddBkErrorPage($exception);
}

/**
 * Deactivates this plugin.
 *
 * @since [*next-version*]
 */
function eddBkDeactivateSelf()
{
    if (!function_exists('deactivate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    deactivate_plugins(plugin_basename(EDDBK_FILE));
}

/**
 * Shows the EDD Bookings exception error page.
 *
 * @since [*next-version*]
 *
 * @param Exception|Throwable $exception The exception.
 */
function eddBkErrorPage($exception)
{
    if (is_admin()) {
        ob_start();
        include EDDBK_DIR . '/templates/error-page.phtml';
        wp_die(
            ob_get_clean(),
            __('EDD Bookings Error', EDDBK_TEXT_DOMAIN),
            array('response' => 500)
        );
    }
}
