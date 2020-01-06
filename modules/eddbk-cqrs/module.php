<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Storage\Resource\Module\EddBkCqrsModule;

define('RC_EDDBK_CQRS_MODULE_DIR', __DIR__);
define('RC_EDDBK_CQRS_MODULE_CONFIG_DIR', RC_EDDBK_CQRS_MODULE_DIR . '/config');
define('RC_EDDBK_CQRS_MODULE_CONFIG_FILE', RC_EDDBK_CQRS_MODULE_CONFIG_DIR . '/config.php');
define('RC_EDDBK_CQRS_MODULE_MIGRATIONS_DIR', RC_EDDBK_CQRS_MODULE_DIR . '/migrations');
define('RC_EDDBK_CQRS_MODULE_KEY', 'eddbk_cqrs');

return function (ContainerInterface $c) {
    return new EddBkCqrsModule(
        RC_EDDBK_CQRS_MODULE_KEY,
        ['wp_bookings_cqrs'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
