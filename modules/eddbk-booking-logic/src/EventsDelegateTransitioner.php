<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Data\StateAwareInterface;
use Dhii\Data\TransitionerAwareTrait;
use Dhii\Data\TransitionerInterface;
use Dhii\Event\EventFactoryInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * A booking transitioner that triggers events before and after a transition, while delegating transitioning to another
 * transitioner instance.
 *
 * @since [*next-version*]
 */
class EventsDelegateTransitioner implements TransitionerInterface
{
    /*
     * Provides awareness of a booking transitioner.
     *
     * @since [*next-version*]
     */
    use TransitionerAwareTrait;

    /*
     * Provides all required functionality for working with events.
     *
     * @since [*next-version*]
     */
    use EventsConsumerTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransitionerInterface      $transitioner The booking transition.
     * @param EventManagerInterface|null $eventManager The event manager, if any.
     * @param EventFactoryInterface|null $eventFactory The event factory, if any.
     */
    public function __construct(
        TransitionerInterface $transitioner,
        $eventManager = null,
        $eventFactory = null
    ) {
        $this->_setTransitioner($transitioner);
        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transition(StateAwareInterface $booking, $transition)
    {
        // Trigger an event before transitioning, filtering the booking
        $beforeEvent = $this->_getEventFactory()->make([
            'name'       => 'before_booking_transition',
            'transition' => $transition,
            'params'     => [
                'booking'    => $booking,
                'transition' => $transition,
            ],
        ]);
        $booking = $this->_trigger($beforeEvent)->getParam('booking');

        // Transition the booking
        $booking = $this->_getTransitioner()->transition($booking, $transition);

        // Trigger an event after transitioning, filtering the booking
        $afterEvent = $this->_getEventFactory()->make([
            'name'       => 'after_booking_transition',
            'transition' => $transition,
            'params'     => [
                'booking'    => $booking,
                'transition' => $transition,
            ],
        ]);
        $booking = $this->_trigger($afterEvent)->getParam('booking');

        return $booking;
    }
}
