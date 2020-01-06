<?php

namespace RebelCode\EddBookings\Cart\Module;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Functionality for normalizing timezone names into a human-readable names.
 *
 * @since [*next-version*]
 */
trait NormalizeTimezoneNameCapableTrait
{
    /**
     * Normalizes a given timezone name into a human-readable name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $tzName The timezone name to normalize.
     *
     * @return string|Stringable The normalized timezone name.
     */
    protected function _normalizeTimezoneName($tzName)
    {
        $tzName = $this->_normalizeString($tzName);

        if ($tzName[0] === '+' || $tzName[0] === '-') {
            return sprintf('UTC%s', $tzName);
        }

        return $tzName;
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see Stringable).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
