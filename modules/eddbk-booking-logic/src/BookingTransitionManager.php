<?php

namespace RebelCode\EddBookings\Logic\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareInterface;
use Dhii\Data\TransitionerInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Events\TransitionEventInterface;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorAwareTrait;
use Dhii\Validation\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\EddBookings\Logic\Module\BookingStatusInterface as S;
use RebelCode\EddBookings\Logic\Module\BookingTransitionInterface as T;
use RebelCode\Modular\Events\EventsConsumerTrait;
use RebelCode\State\PossibleTransitionsAwareTrait;
use stdClass;

/**
 * Manages the booking transitions for EDD Bookings.
 *
 * @since [*next-version*]
 */
class BookingTransitionManager implements InvocableInterface
{
    /*
     * Provides awareness of a validator.
     *
     * @since [*next-version*]
     */
    use ValidatorAwareTrait {
        _getValidator as _getBookingValidator;
        _setValidator as _setBookingValidator;
    }

    /*
     * Provides awareness of configuration.
     *
     * @since [*next-version*]
     */
    use PossibleTransitionsAwareTrait;

    /*
     * Provides functionality for reading from any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides container normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not-found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides all required functionality for working with events.
     *
     * @since [*next-version*]
     */
    use EventsConsumerTrait;

    /**
     * The booking transitioner.
     *
     * @since [*next-version*]
     *
     * @var TransitionerInterface
     */
    protected $transitioner;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ValidatorInterface                            $bookingValidator The booking validator.
     * @param TransitionerInterface                         $transitioner     The booking transitioner.
     * @param EventManagerInterface                         $eventManager     The event manager.
     * @param EventFactoryInterface                         $eventFactory     The event factory.
     * @param array|ArrayAccess|stdClass|ContainerInterface $transitions      The possible transitions.
     */
    public function __construct(
        ValidatorInterface $bookingValidator,
        TransitionerInterface $transitioner,
        EventManagerInterface $eventManager,
        EventFactoryInterface $eventFactory,
        $transitions
    ) {
        $this->_setBookingValidator($bookingValidator);
        $this->_setTransitioner($transitioner);
        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
        $this->_setPossibleTransitions($transitions);
    }

    /**
     * Retrieves the booking transitioner.
     *
     * @since [*next-version*]
     *
     * @return TransitionerInterface The booking transitioner instance.
     */
    protected function _getTransitioner()
    {
        return $this->transitioner;
    }

    /**
     * Sets the booking transitioner.
     *
     * @since [*next-version*]
     *
     * @param TransitionerInterface $transitioner The booking transitioner instance.
     */
    protected function _setTransitioner(TransitionerInterface $transitioner)
    {
        $this->transitioner = $transitioner;
    }

    /**
     * Retrieves the possible transitions from a given booking status.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $status The booking status.
     *
     * @return array|ArrayAccess|stdClass|ContainerInterface A container of transition-to-status mappings.
     */
    protected function _getPossibleTransitionsForStatus($status)
    {
        $transitions = $this->_getPossibleTransitions();
        $status      = ($status === null) ? S::STATUS_NONE : $status;

        try {
            return $this->_containerGet($transitions, $status);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return [];
        }
    }

    /**
     * Retrieves the status that would result from a successful transition from a particular status.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $status     The booking status.
     * @param string|Stringable $transition The transition.
     *
     * @throws NotFoundExceptionInterface If the status or transition are not found in the config.
     *
     * @return string|Stringable The new booking status.
     */
    protected function _getStatusAfterTransition($status, $transition)
    {
        $transitions = $this->_getPossibleTransitionsForStatus($status);

        return $this->_containerGet($transitions, $transition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $this->_attach('on_booking_transition', [$this, '_limitTransitionsToConfig']);
        $this->_attach('on_booking_transition', [$this, '_validateBookingOnTransition']);
        $this->_attach('on_booking_transition', [$this, '_limitCompleteBookingTransition']);
        $this->_attach('after_booking_transition', [$this, '_afterPendingTransition']);
        $this->_attach('after_booking_transition', [$this, '_afterApprovedTransition']);
    }

    /**
     * Limits the allowed transitions for bookings to only those declared in the module config.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    public function _limitTransitionsToConfig(TransitionEventInterface $event)
    {
        $booking = $event->getParam('booking');

        if (!($booking instanceof BookingInterface)) {
            throw $this->_createRuntimeException($this->__('Transition does not have a valid booking instance'));
        }

        if (!($booking instanceof StateAwareInterface)) {
            throw $this->_createRuntimeException($this->__('Booking in transition is not a state-aware instance'));
        }

        $status     = $booking->getState()->get('status');
        $status     = empty($status) ? S::STATUS_NONE : $status;
        $transition = $event->getTransition();

        try {
            $newStatus = $this->_getStatusAfterTransition($status, $transition);
            $newParams = ['new_state' => $newStatus] + $event->getParams();
            $event->setParams($newParams);
        } catch (NotFoundExceptionInterface $notFoundException) {
            $event->abortTransition(true);
            $event->stopPropagation(true);
        }
    }

    /**
     * Validates bookings on certain transitions.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    public function _validateBookingOnTransition(TransitionEventInterface $event)
    {
        $transition = $event->getTransition();

        if (
            $transition === T::TRANSITION_DRAFT ||
            $transition === T::TRANSITION_CART ||
            $transition === T::TRANSITION_SUBMIT ||
            $transition === T::TRANSITION_SCHEDULE
        ) {
            $this->_validateBookingInTransitionEvent($event);
        }
    }

    /**
     * Limits the booking `complete` transition to past bookings only.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    public function _limitCompleteBookingTransition(TransitionEventInterface $event)
    {
        $transition = $event->getTransition();

        if ($transition === T::TRANSITION_COMPLETE) {
            $booking = $event->getParam('booking');

            if (!($booking instanceof BookingInterface)) {
                throw $this->_createOutOfRangeException(
                    $this->__('Transition event does not contain a booking'), null, null, null
                );
            }

            if ($booking->getStart() > time()) {
                $event->abortTransition(true);

                throw $this->_createRuntimeException(
                    $this->__('Only past bookings can be transitioned with `complete`'), null, null
                );
            }
        }
    }

    /**
     * Attempts an approval transition after a booking is submitted.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    public function _afterPendingTransition(TransitionEventInterface $event)
    {
        if ($event->getTransition() === T::TRANSITION_SUBMIT) {
            $booking = $event->getParam('booking');
            $booking = $this->_getTransitioner()->transition($booking, T::TRANSITION_APPROVE);

            $event->setParams(['booking' => $booking] + $event->getParams());
        }
    }

    /**
     * Attempts a schedule transition after a booking is approved.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    public function _afterApprovedTransition(TransitionEventInterface $event)
    {
        if ($event->getTransition() === T::TRANSITION_APPROVE) {
            $booking = $event->getParam('booking');
            $booking = $this->_getTransitioner()->transition($booking, T::TRANSITION_SCHEDULE);

            $event->setParams(['booking' => $booking] + $event->getParams());
        }
    }

    /**
     * Validates a booking in a transition event.
     *
     * @since [*next-version*]
     *
     * @param TransitionEventInterface $event The transition event.
     */
    protected function _validateBookingInTransitionEvent(TransitionEventInterface $event)
    {
        /* @var $booking */
        $booking = $event->getParam('booking');

        if (!($booking instanceof BookingInterface)) {
            throw $this->_createOutOfRangeException(
                $this->__('Transition event does not contain a booking'), null, null, null
            );
        }

        try {
            $this->_getBookingValidator()->validate($booking);
        } catch (ValidationFailedExceptionInterface $validationFailedException) {
            $event->abortTransition(true);
            $event->stopPropagation(true);
            throw $validationFailedException;
        }
    }
}
