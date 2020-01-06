<?php

use Psr\Container\ContainerInterface;
use RebelCode\Storage\Resource\WordPress\Module\WpCqrsModule;

define('RC_WP_CQRS_MODULE_DIR', __DIR__);
define('RC_WP_CQRS_MODULE_KEY', 'wp_cqrs');

return function (ContainerInterface $c) {
    return new WpCqrsModule(
        RC_WP_CQRS_MODULE_KEY,
        [],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory')
    );
};
