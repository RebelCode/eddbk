<?php

namespace RebelCode\EddBookings\Cart;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareInterface;
use Dhii\Data\ValueAwareInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateCouldNotMakeExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use RebelCode\Bookings\BookingInterface;

/**
 * The factory for creating booking value-aware objects.
 *
 * @since [*next-version*]
 */
class BookingValueAwareFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotMakeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The key in the config where the booking is retrieved from.
     *
     * @since [*next-version*]
     */
    const K_BOOKING = 'booking';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return ValueAwareInterface The created booking value aware object.
     */
    public function make($config = null)
    {
        if ($config == null || !$this->_containerHas($config, static::K_BOOKING)) {
            throw $this->_createCouldNotMakeException(
                $this->__('Missing booking in factory config'), null, null, $config
            );
        }

        $booking = $this->_containerGet($config, static::K_BOOKING);

        if (!($booking instanceof StateAwareInterface) || !($booking instanceof BookingInterface)) {
            throw $this->_createCouldNotMakeException(
                $this->__('Booking in factory config is not a state-aware booking'),
                null,
                null,
                $config
            );
        }

        return new BookingValueAware($booking);
    }
}
