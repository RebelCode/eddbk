<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Provides functionality for awareness of rule repetition frequency.
 *
 * @since [*next-version*]
 */
trait RepeatFreqAwareTrait
{
    /**
     * How frequently the rule repeats, as a unit-less integer; zero or null for no repetition.
     *
     * @since [*next-version*]
     *
     * @var int|null
     */
    protected $repeatFreq;

    /**
     * Retrieves how frequently the rule repeats.
     *
     * @since [*next-version*]
     *
     * @return int|null The number of a particular unit from the rule start timestamp until the rule repeats, or zero
     *                  or null for no repetition.
     */
    protected function _getRepeatFreq()
    {
        return $this->repeatFreq;
    }

    /**
     * Sets how frequently the rule repeats.
     *
     * @since [*next-version*]
     *
     * @param int|null $repeatFreq The number of particular unit from the rule start timestamp until the rule repeats.
     *                             Zero or null for no repetition.
     */
    protected function _setRepeatFreq($repeatFreq)
    {
        if ($repeatFreq === null) {
            $repeatFreq = null;

            return;
        }

        $this->repeatFreq = $this->_normalizeInt($repeatFreq);
    }

    /**
     * Normalizes a value into an integer.
     *
     * The value must be a whole number, or a string representing such a number,
     * or an object representing such a string.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|float|int $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return int The normalized value.
     */
    abstract protected function _normalizeInt($value);
}
