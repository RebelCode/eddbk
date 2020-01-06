<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Module\EddBkRestApiModule;

define('RCMOD_EDDBK_REST_API_DIR', __DIR__);
define('RCMOD_EDDBK_REST_API_CONFIG_DIR', __DIR__ . '/config');
define('RCMOD_EDDBK_REST_API_CONFIG_FILE', RCMOD_EDDBK_REST_API_CONFIG_DIR . '/config.php');
define('RCMOD_EDDBK_REST_API_KEY', 'eddbk_rest_api');

return function(ContainerInterface $c) {
    return new EddBkRestApiModule(
        RCMOD_EDDBK_REST_API_KEY,
        ['eddbk_cqrs'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
