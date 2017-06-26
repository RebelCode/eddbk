<?php

use RebelCode\EddBookings\Core\Di\Container;
use RebelCode\EddBookings\Core\Di\MainServiceProvider;
use RebelCode\EddBookings\Core\Modular\ModuleSystemServiceProvider;
use RebelCode\EddBookings\Core\Plugin;
use RebelCode\EddBookings\Core\PluginInterface;

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
        $container = new Container();
        $instance  = new Plugin($container, $container);

        // Service providers
        $mainSp    = new MainServiceProvider();
        $moduleSp  = new ModuleSystemServiceProvider(
            MainServiceProvider::SID_FACTORY,
            MainServiceProvider::SID_LOOP_MACHINE
        );

        $container->register($mainSp);
        $container->register($moduleSp);
    }

    return $instance;
}
