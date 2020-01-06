<?php

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Logic\Module\EddBkBookingLogicModule;

define('EDDBK_BOOKING_LOGIC_MODULE_DIR', __DIR__);
define('EDDBK_BOOKING_LOGIC_MODULE_CONFIG', __DIR__ . '/config.php');
define('EDDBK_BOOKING_LOGIC_MODULE_KEY', 'eddbk_booking_logic');

return function (ContainerInterface $c) {
    return new EddBkBookingLogicModule(
        EDDBK_BOOKING_LOGIC_MODULE_KEY,
        ['wp_bookings_cqrs', 'booking_logic'],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
