<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\Sessions\SessionGenerator;
use RebelCode\EddBookings\Sessions\Generator\FixedDurationSessionTypeFactory;
use RebelCode\EddBookings\Sessions\Generator\SessionTypeFactory;
use RebelCode\EddBookings\Sessions\Module\AvailabilityFactory;
use RebelCode\EddBookings\Sessions\Module\GenerateSessionsHandler;
use RebelCode\EddBookings\Sessions\Time\PeriodFactory;

return [
    'eddbk_session_generator' => function (ContainerInterface $c) {
        return new SessionGenerator();
    },

    'eddbk_generate_sessions_handler' => function (ContainerInterface $c) {
        return new GenerateSessionsHandler(
            $c->get('eddbk_session_generator'),
            $c->get('eddbk_services_manager'),
            $c->get('resources_entity_manager'),
            $c->get('eddbk_session_type_factory'),
            $c->get('eddbk_availability_factory'),
            $c->get('sessions_insert_rm'),
            $c->get('sessions_delete_rm'),
            $c->get('sql_expression_builder')
        );
    },

    'eddbk_availability_factory' => function (ContainerInterface $c) {
        return new AvailabilityFactory();
    },

    'eddbk_session_type_factory' => function (ContainerInterface $c) {
        $factoriesCfg = $c->get('eddbk/session_generator/session_type_factories');

        $factories = [];
        foreach ($factoriesCfg as $_type => $_service) {
            $factories[$_type] = $c->get($_service);
        }

        return new SessionTypeFactory($factories);
    },

    'eddbk_fixed_duration_session_type_factory' => function (ContainerInterface $c) {
        return new FixedDurationSessionTypeFactory();
    },

    'eddbk_period_factory' => function (ContainerInterface $c) {
        return new PeriodFactory();
    },
];
