<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Provides functionality for awareness of a list of excluded rule repetition dates.
 *
 * @since [*next-version*]
 */
trait ExcludedDatesAwareTrait
{
    /**
     * A list of dates to exclude.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $excludedDates;

    /**
     * Retrieves the container of excluded timestamps.
     *
     * @since [*next-version*]
     *
     * @return array A list of timestamps to exclude.
     */
    protected function _getExcludedDates()
    {
        return $this->excludedDates;
    }

    /**
     * Sets the container of excluded timestamps.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $excludedDates A list of timestamps to exclude.
     */
    protected function _setExcludedDates($excludedDates)
    {
        $this->excludedDates = $this->_normalizeArray($excludedDates);
    }

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);
}
