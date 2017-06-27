<?php

use RebelCode\EddBookings\Core\Di\Container;
use RebelCode\EddBookings\Core\Di\MainServiceProvider;
use RebelCode\EddBookings\Core\Di\ModuleSystemServiceProvider;
use RebelCode\EddBookings\Core\Modular\Module\Plugin;
use RebelCode\EddBookings\Core\Modular\Module\PluginInterface;

/**
 * Retrieves the EDD Bookings plugin hub instance.
 *
 * @since[*next-version*]
 *
 * @return PluginInterface
 */
function eddbk()
{
    static $instance = null;

    if ($instance === null) {
        // Create container and service providers
        $container = new Container();
        $mainSp    = new MainServiceProvider();
        $moduleSp  = new ModuleSystemServiceProvider(
            MainServiceProvider::SID_FACTORY,
            MainServiceProvider::SID_LOOP_MACHINE
        );
        // Register service providers
        $container->register($mainSp);
        $container->register($moduleSp);

        $instance = $container->get(MainServiceProvider::SID_PLUGIN);
    }

    return $instance;
}
