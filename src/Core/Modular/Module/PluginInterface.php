<?php

namespace RebelCode\EddBookings\Core\Modular\Module;

use Dhii\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Modular\Module\ModuleInterface;

/**
 * Something that represents a WordPress plugin.
 *
 * @since[*next-version*]
 */
interface PluginInterface extends ModuleInterface
{
    /**
     * Retrieves the user-friendly name of the plugin.
     *
     * @since[*next-version*]
     *
     * @return string
     */
    public function getName();

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
}
