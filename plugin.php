<?php

/*
 * @wordpress-plugin
 *
 * Plugin Name: Easy Digital Downloads - Bookings
 * Plugin URI: https://eddbookings.com
 * Description: Adds a customizable booking system to Easy Digital Downloads.
 * Version: 0.3-dev7
 * Author: RebelCode
 * Author URI: http://rebelcode.com
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

use Dhii\Collection\CountableMap;
use Dhii\Config\DereferencingConfigMapFactory;
use Dhii\EventManager\WordPress\WpEventManager;
use Dhii\Exception\InternalException;
use Dhii\Modular\Module\ModuleInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\EddBookings\Core\Di\CompositeContainerFactory;
use RebelCode\EddBookings\Core\Di\ContainerFactory;
use RebelCode\EddBookings\Core\ExceptionHandler;
use RebelCode\EddBookings\Core\PluginModule;
use RebelCode\Modular\Events\EventFactory;
use RebelCode\Modular\Finder\ModuleFileFinder;

// Plugin info
define('EDDBK_SLUG', 'eddbk');
define('EDDBK_MIN_PHP_VERSION', '5.4.0');
define('EDDBK_MIN_WP_VERSION', '4.4');
define('EDDBK_MIN_EDD_VERSION', '2.6.0');
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
 *
 * @throws InternalException If the event manager failed to initialize.
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
         * The factory for creating configs.
         * Used by the plugin's modular system, as well as by modules.
         */
        $configFactory = new DereferencingConfigMapFactory();
        $configFactory = apply_filters('eddbk_core_module_config_factory', $configFactory);

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
         * The event manager.
         * Used for managing WordPress hooks as events.
         */
        $eventManager = new WpEventManager(true);
        $eventManager = apply_filters('eddbk_core_module_event_manager', $eventManager);

        /*
         * The event factory.
         * Used in conjunction with the event manager for creating events.
         */
        $eventFactory = new EventFactory();
        $eventFactory = apply_filters('eddbk_core_module_event_factory', $eventFactory);

        /*
         * The core plugin module.
         * This is a special module that loads other modules.
         */
        $coreModule = new PluginModule(
            getEddBkInfo(),
            $configFactory,
            $containerFactory,
            $compContainerFactory,
            $eventManager,
            $eventFactory,
            $fileFinder
        );
        $coreModule = apply_filters('eddbk_core_module', $coreModule);

        // Safety check - in case a filter did something wonky
        if (!$coreModule instanceof ModuleInterface) {
            throw new OutOfRangeException(__('Core module is not a module instance.', EDDBK_TEXT_DOMAIN));
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
        function () use ($container) {
            eddBkCheckDependencies();

            getEddBkCore()->run($container);
        },
        0
    );
}


/**
 * Retrieves the plugin info.
 *
 * @since [*next-version*]
 *
 * @return CountableMap
 */
function getEddBkInfo()
{
    if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }

    $pluginData = get_plugin_data(__FILE__);

    return new CountableMap([
        'slug'               => 'eddbk',
        'name'               => $pluginData['Name'],
        'description'        => $pluginData['Description'],
        'uri'                => $pluginData['PluginURI'],
        'version'            => $pluginData['Version'],
        'author'             => $pluginData['Author'],
        'author_uri'         => $pluginData['AuthorURI'],
        'text_domain'        => $pluginData['TextDomain'],
        'config_file_path'   => __DIR__ . '/config.php',
        'services_file_path' => __DIR__ . '/services.php',
        'file_path'          => __FILE__,
        'directory'          => __DIR__,
    ]);
}

/**
 * Checks the required dependencies for EDD Bookings, deactivating with a message if not satisfied.
 *
 * @since [*next-version*]
 */
function eddBkCheckDependencies()
{
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), EDDBK_MIN_WP_VERSION) < 0) {
        $reason = __(
            'EDD Bookings requires WordPress at version %1$s or later',
            EDDBK_TEXT_DOMAIN
        );
        eddBkDeactivateSelf(sprintf($reason, EDDBK_MIN_WP_VERSION));

        return;
    }

    if (!defined('EDD_VERSION') || version_compare(EDD_VERSION, EDDBK_MIN_EDD_VERSION) < 0) {
        $reason = __(
            'EDD Bookings requires the Easy Digital Downloads plugin to be installed and activated at version %1$s or later',
            EDDBK_TEXT_DOMAIN
        );
        eddBkDeactivateSelf(sprintf($reason, EDDBK_MIN_EDD_VERSION));

        return;
    }
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
 *
 * @param string|Stringable|null $reason A string containing the reason for deactivation. If not given, the
 *                                       plugin will be deactivated silently. Default: null
 */
function eddBkDeactivateSelf($reason = null)
{
    if (!function_exists('deactivate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    deactivate_plugins(plugin_basename(EDDBK_FILE));

    if (is_null($reason)) {
        return;
    }

    $title   = __('EDD Bookings has been deactivated!', EDDBK_TEXT_DOMAIN);
    $message = sprintf('<h1>%s</h1><p>%s</p>', $title, strval($reason));

    // Show wp_die screen with back link
    wp_die(
        $message,
        $title,
        array('back_link' => true)
    );
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
