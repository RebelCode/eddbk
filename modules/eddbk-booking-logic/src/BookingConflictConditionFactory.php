<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateCouldNotMakeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateFactoryExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RebelCode\Bookings\BookingInterface;
use stdClass;
use Traversable;

/**
 * The factory that creates the condition that is used to query for bookings that conflict with a specific booking.
 *
 * @since [*next-version*]
 */
class BookingConflictConditionFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateFactoryExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotMakeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * A list of non-blocking booking statuses.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $nonBlockingStatuses;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param object                     $exprBuilder         The expression builder.
     * @param array|stdClass|Traversable $nonBlockingStatuses The non-blocking booking statuses.
     */
    public function __construct($exprBuilder, $nonBlockingStatuses)
    {
        $this->exprBuilder = $exprBuilder;
        $this->_setNonBlockingStatuses($nonBlockingStatuses);
    }

    /**
     * Retrieves the non-blocking booking statuses.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable The non-blocking booking statuses.
     */
    protected function _getNonBlockingStatuses()
    {
        return $this->nonBlockingStatuses;
    }

    /**
     * Sets the non-blocking booking statuses.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $nonBlockingStatuses The non-blocking booking statuses.
     *
     * @throws InvalidArgumentException If the argument is not a traversable list.
     */
    protected function _setNonBlockingStatuses($nonBlockingStatuses)
    {
        $this->nonBlockingStatuses = $this->_normalizeIterable($nonBlockingStatuses);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $booking = $this->_containerGet($config, 'booking');

        if (!($booking instanceof BookingInterface)) {
            throw $this->_createOutOfRangeException(
                $this->__('Booking in factory config is not a valid booking instance'), null, null, $booking
            );
        }

        $b = $this->exprBuilder;

        $s1 = $b->lit($booking->getStart());
        $s2 = $b->ef('booking', 'start');
        $e1 = $b->lit($booking->getEnd());
        $e2 = $b->ef('booking', 'end');

        // This booking starts within range of another booking in storage
        // or a booking in storage starts within range of this booking
        $condition = $b->or(
            $b->and(
                $b->gte($s1, $s2),
                $b->lt($s1, $e2)
            ),
            $b->and(
                $b->gte($s2, $s1),
                $b->lt($s2, $e1)
            )
        );

        $id = $booking->getId();

        if (!empty($id)) {
            $condition = $b->and(
                // Booking overlaps
                $condition,
                // and is not this booking
                $b->not(
                    $b->eq(
                        $b->ef('booking', 'id'),
                        $b->lit($booking->getId())
                    )
                )
            );
        }

        $nonBlockingStatuses = $this->_getNonBlockingStatuses();
        $nonBlockingStatuses = $this->_normalizeArray($nonBlockingStatuses);

        if (count($nonBlockingStatuses) > 0) {
            // AND the condition to a "booking status NOT IN (...)" sub-condition, where "..." is the list of non-
            // blocking booking statuses, to make the condition disregard bookings with those statuses.
            $condition = $b->and(
                $condition,
                $b->not(
                    $b->in(
                        $b->ef('booking', 'status'),
                        $b->set($nonBlockingStatuses)
                    )
                )
            );
        }

        foreach ($booking->getResourceIds() as $resourceId) {
            $condition = $b->and(
                $condition,
                // The resource is in the list of resources for the booking in the DB
                // The FIND_IN_SET(needle, haystack) MySQL function takes 2 strings and checks if the needle string
                // is found in the haystack string comma separated list (without whitespace).
                $b->fn(
                    'FIND_IN_SET',
                    $b->lit($resourceId),
                    $b->var('resource_ids')
                )
            );
        }

        return $condition;
    }
}
