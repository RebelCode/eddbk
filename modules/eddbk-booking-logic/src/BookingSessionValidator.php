<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Validation\AbstractValidatorBase;
use RebelCode\Bookings\BookingInterface;

/**
 * A booking validator that validates whether a booking matches an existing session.
 *
 * @since [*next-version*]
 */
class BookingSessionValidator extends AbstractValidatorBase
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * The SELECT resource model for sessions.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $sessionsSelectRm;

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
     * @param SelectCapableInterface $sessionsSelectRm The SELECT resource model for sessions.
     * @param object                 $exprBuilder      The expression builder.
     */
    public function __construct($sessionsSelectRm, $exprBuilder)
    {
        $this->_setSessionsSelectRm($sessionsSelectRm);
        $this->_setExpressionBuilder($exprBuilder);
    }

    /**
     * Retrieves the sessions SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface The sessions SELECT resource model instance.
     */
    protected function _getSessionsSelectRm()
    {
        return $this->sessionsSelectRm;
    }

    /**
     * Sets the sessions SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $sessionsSelectRm The sessions SELECT resource model instance.
     */
    protected function _setSessionsSelectRm($sessionsSelectRm)
    {
        if ($sessionsSelectRm !== null && !($sessionsSelectRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $sessionsSelectRm
            );
        }

        $this->sessionsSelectRm = $sessionsSelectRm;
    }

    /**
     * Retrieves the expression builder.
     *
     * @since [*next-version*]
     *
     * @return object The expression builder instance.
     */
    protected function _getExpressionBuilder()
    {
        return $this->exprBuilder;
    }

    /**
     * Sets the expression builder.
     *
     * @since [*next-version*]
     *
     * @param object $exprBuilder The expression builder instance.
     */
    protected function _setExpressionBuilder($exprBuilder)
    {
        if (!is_object($exprBuilder)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an expression builder.'),
                null,
                null,
                $exprBuilder
            );
        }

        $this->exprBuilder = $exprBuilder;
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

        $errors     = [];
        $condition  = $this->_buildBookingSessionCondition($booking);
        $matching   = $this->_getSessionsSelectRm()->select($condition);
        $numMatches = $this->_countIterable($matching);

        if ($numMatches === 0) {
            $errors[] = $this->__('The booking does not match any session');
        }

        return $errors;
    }

    /**
     * Builds the condition for retrieving the matching session for a booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface $booking The booking instance for which to retrieve a matching session.
     *
     * @return LogicalExpressionInterface The condition.
     */
    protected function _buildBookingSessionCondition(BookingInterface $booking)
    {
        $b = $this->_getExpressionBuilder();

        $s1 = $b->lit($booking->getStart());
        $s2 = $b->ef('session', 'start');
        $e1 = $b->lit($booking->getEnd());
        $e2 = $b->ef('session', 'end');

        // Booking and session have identical start and end times
        $matchingTime = $b->and(
            $b->eq($s1, $s2),
            $b->eq($e1, $e2)
        );

        $matchingResource = null;
        foreach ($booking->getResourceIds() as $resourceId) {
            // The resource is in the list of resources for the session
            // The FIND_IN_SET(needle, haystack) MySQL function takes 2 strings and checks if the needle string
            // is found in the haystack string comma separated list (without whitespace).
            $r = $b->fn(
                'FIND_IN_SET',
                $b->lit($resourceId),
                $b->ef('session', 'resource_ids')
            );
            $matchingResource = ($matchingResource !== null)
                ? $b->and($matchingResource, $r)
                : $r;
        }

        return $b->and(
            $matchingTime,
            $matchingResource
        );
    }
}
