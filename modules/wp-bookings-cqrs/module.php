<?php

use Psr\Container\ContainerInterface;
use RebelCode\Storage\Resource\WordPress\Module\WpBookingsCqrsModule;

return function (ContainerInterface $c) {
    return new WpBookingsCqrsModule(
        'wp_bookings_cqrs',
        __DIR__,
        ['wp_cqrs'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
