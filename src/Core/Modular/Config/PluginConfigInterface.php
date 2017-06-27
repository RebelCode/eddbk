<?php

namespace RebelCode\EddBookings\Core\Modular\Config;

use RebelCode\Modular\Config\ConfigInterface;

/**
 * Configuration for a WordPress plugin.
 *
 * @since[*next-version*]
 */
interface PluginConfigInterface extends ConfigInterface
{
    /**
     * The key of the plugin name in configuration data.
     *
     * @since [*next-version*]
     */
    const K_NAME = 'title';

    /**
     * The key of the dependency slug in the dependency sub-configuration data.
     *
     * @since [*next-version*]
     */
    const K_DEPENDENCY_SLUG = 'slug';

    /**
     * The key of the dependency name in the dependency sub-configuration data.
     *
     * @since [*next-version*]
     */
    const K_DEPENDENCY_NAME = 'name';

    /**
     * The key of the dependency version in the dependency sub-configuration data.
     *
     * @since [*next-version*]
     */
    const K_DEPENDENCY_VERSION = 'version';
}
