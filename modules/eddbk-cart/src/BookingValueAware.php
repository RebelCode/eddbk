<?php

namespace RebelCode\EddBookings\Cart;

use Dhii\Data\StateAwareInterface;
use Dhii\Data\ValueAwareInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use RebelCode\Bookings\BookingInterface;

/**
 * An implementation of an object that can provide a booking instance.
 *
 * @since [*next-version*]
 */
class BookingValueAware implements ValueAwareInterface
{
    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The state aware.
     *
     * @since [*next-version*]
     *
     * @var BookingInterface|StateAwareInterface
     */
    protected $booking;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking instance. Must also be state-aware.
     */
    public function __construct(BookingInterface $booking)
    {
        $this->booking = $booking;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return BookingInterface|StateAwareInterface
     */
    public function getValue()
    {
        return $this->booking;
    }
}
