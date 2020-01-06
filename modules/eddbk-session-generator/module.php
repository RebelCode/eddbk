<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Sessions\Module\EddBkSessionGeneratorModule;

define('RCMOD_EDDBK_SESSION_GENERATOR_DIR', __DIR__);
define('RCMOD_EDDBK_SESSION_GENERATOR_SERVICES_FILE', RCMOD_EDDBK_SESSION_GENERATOR_DIR . '/services.php');
define('RCMOD_EDDBK_SESSION_GENERATOR_CONFIG_DIR', RCMOD_EDDBK_SESSION_GENERATOR_DIR);
define('RCMOD_EDDBK_SESSION_GENERATOR_CONFIG_FILE', RCMOD_EDDBK_SESSION_GENERATOR_CONFIG_DIR . '/config.php');
define('RCMOD_EDDBK_SESSION_GENERATOR_KEY', 'eddbk_session_generator');

return function(ContainerInterface $c) {
    return new EddBkSessionGeneratorModule(
        RCMOD_EDDBK_SESSION_GENERATOR_KEY,
        [],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
