<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Util\String\StringableInterface;

/**
 * The booking transitions in EDD Bookings.
 *
 * @since [*next-version*]
 */
interface BookingTransitionInterface extends StringableInterface
{
    /**
     * The transition key for bookings being placed into the shopping cart.
     *
     * @since [*next-version*]
     */
    const TRANSITION_CART = 'cart';

    /**
     * The transition key for drafting a booking.
     *
     * @since [*next-version*]
     */
    const TRANSITION_DRAFT = 'draft';

    /**
     * The transition key for submitting a booking into the system.
     *
     * @since [*next-version*]
     */
    const TRANSITION_SUBMIT = 'submit';

    /**
     * The transition key for bookings being approved by the system.
     *
     * @since [*next-version*]
     */
    const TRANSITION_APPROVE = 'approve';

    /**
     * The transition key for bookings being rejected by the system.
     *
     * @since [*next-version*]
     */
    const TRANSITION_REJECT = 'reject';

    /**
     * The transition key for bookings being scheduled.
     *
     * @since [*next-version*]
     */
    const TRANSITION_SCHEDULE = 'schedule';

    /**
     * The transition key for bookings being cancelled.
     *
     * @since [*next-version*]
     */
    const TRANSITION_CANCEL = 'cancel';

    /**
     * The transition key for bookings being marked as completed.
     *
     * @since [*next-version*]
     */
    const TRANSITION_COMPLETE = 'complete';
}
