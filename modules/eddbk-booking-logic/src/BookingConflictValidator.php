<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Validation\AbstractValidatorBase;
use Dhii\Validation\ValidatorInterface;
use RebelCode\Bookings\BookingInterface;

/**
 * A booking validator that validates whether a booking conflicts with another.
 *
 * @since [*next-version*]
 */
class BookingConflictValidator extends AbstractValidatorBase implements ValidatorInterface
{
    /* @since [*next-version*] */
    use FactoryAwareTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * The SELECT resource model for bookings.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $bookingsSelectRm         The SELECT resource model for bookings.
     * @param FactoryInterface       $conflictConditionFactory The factory that creates the booking conflict condition.
     */
    public function __construct($bookingsSelectRm, $conflictConditionFactory)
    {
        $this->_setBookingsSelectRm($bookingsSelectRm);
        $this->_setFactory($conflictConditionFactory);
    }

    /**
     * Retrieves the bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface The bookings SELECT resource model instance.
     */
    protected function _getBookingsSelectRm()
    {
        return $this->bookingsSelectRm;
    }

    /**
     * Sets the bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $bookingsSelectRm The bookings SELECT resource model instance.
     */
    protected function _setBookingsSelectRm($bookingsSelectRm)
    {
        if ($bookingsSelectRm !== null && !($bookingsSelectRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $bookingsSelectRm
            );
        }

        $this->bookingsSelectRm = $bookingsSelectRm;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getValidationErrors($booking)
    {
        if (!($booking instanceof BookingInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Subject is not a booking instance'), null, null, $booking
            );
        }

        $errors       = [];
        $condition    = $this->_getFactory()->make(['booking' => $booking]);
        $conflicts    = $this->_getBookingsSelectRm()->select($condition);
        $numConflicts = count($conflicts);

        if ($numConflicts > 0) {
            $errors[] = $this->__('The booking conflicts with %d other booking(s)', [$numConflicts]);
        }

        return $errors;
    }
}
