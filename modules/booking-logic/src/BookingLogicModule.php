<?php

namespace RebelCode\Bookings\Module;

use Dhii\Collection\CountableMapFactory;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use RebelCode\Bookings\BookingFactory;
use RebelCode\Bookings\Transitioner\BookingTransitioner;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\State\EventStateMachineFactory;

/**
 * Module class for the booking logic module.
 *
 * @since [*next-version*]
 */
class BookingLogicModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RC_BOOKING_LOGIC_MODULE_CONFIG),
            [
                /**
                 * Factory for creating bookings; specifically, state-aware bookings.
                 *
                 * @since [*next-version*]
                 */
                'booking_factory'                            => function (ContainerInterface $c) {
                    return new BookingFactory(
                        $c->get('map_factory')
                    );
                },
                /**
                 * Factory for creating maps.
                 *
                 * @since [*next-version*]
                 */
                'map_factory'                                => function (ContainerInterface $c) {
                    return new CountableMapFactory();
                },
                /**
                 * The booking transitioner instance.
                 *
                 * @since [*next-version*]
                 */
                'booking_transitioner'                       => function (ContainerInterface $c) {
                    return new BookingTransitioner(
                        $c->get('booking_logic/status_transitions'),
                        $c->get('booking_transitioner_state_machine_factory'),
                        $c->get('booking_factory'),
                        $c->get('booking_logic/transitioner/state_key')
                    );
                },
                /**
                 * The factory for creating state machines for the booking transitioner during transitions.
                 *
                 * @since [*next-version*]
                 */
                'booking_transitioner_state_machine_factory' => function (ContainerInterface $c) {
                    return new EventStateMachineFactory(
                        $c->get('event_manager'),
                        $c->get('booking_transition_event_factory'),
                        $c->get('booking_logic/transition_event_format')
                    );
                },
                /**
                 * The factory for booking transitioner state machine to be able to create transition events.
                 *
                 * @since [*next-version*]
                 */
                'booking_transition_event_factory'           => function (ContainerInterface $c) {
                    return new TransitionEventFactory();
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
    }
}
