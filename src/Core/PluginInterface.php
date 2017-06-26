<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Something that represents a WordPress plugin.
 *
 * @since[*next-version*]
 */
interface PluginInterface
{
    /**
     * Retrieves the plugin container instance.
     *
     * @since[*next-version*]
     *
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * Retrieves the plugin's factory instance.
     *
     * @since[*next-version*]
     *
     * @return FactoryInterface
     */
    public function getFactory();

    /**
     * Begins execution of the plugin.
     *
     * @since[*next-version*]
     */
    public function run();
}
