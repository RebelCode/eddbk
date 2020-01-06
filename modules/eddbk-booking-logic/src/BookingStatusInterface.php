<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Util\String\StringableInterface;

/**
 * The booking statuses in EDD Bookings.
 *
 * @since [*next-version*]
 */
interface BookingStatusInterface extends StringableInterface
{
    /**
     * The status key for new state-less bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_NONE = 'none';

    /**
     * The status key for bookings in the cart.
     *
     * @since [*next-version*]
     */
    const STATUS_IN_CART = 'in_cart';

    /**
     * The status key for admin-saved draft bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_DRAFT = 'draft';

    /**
     * The status key for bookings that are pending approval.
     *
     * @since [*next-version*]
     */
    const STATUS_PENDING = 'pending';

    /**
     * The status key for approved bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_APPROVED = 'approved';

    /**
     * The status key for scheduled bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_SCHEDULED = 'scheduled';

    /**
     * The status key for rejected bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_REJECTED = 'rejected';

    /**
     * The status key for past completed bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_COMPLETED = 'completed';

    /**
     * The status key for cancelled bookings.
     *
     * @since [*next-version*]
     */
    const STATUS_CANCELLED = 'cancelled';
}
