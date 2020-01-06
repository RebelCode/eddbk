<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Help\HelpModule;

return function (ContainerInterface $c) {
    return new HelpModule(
        [
            'key'                => 'eddbk_help',
            'dependencies'       => [],
            'module_dir'         => 'rcmod-eddbk-help',
            'module_dir_path'    => __DIR__,
            'config_file_path'   => __DIR__ . '/config.php',
            'services_file_path' => __DIR__ . '/services.php',
        ],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
