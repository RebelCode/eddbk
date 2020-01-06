<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Provides functionality for awareness of the timestamp at which rule repetition ends.
 *
 * @since [*next-version*]
 */
trait RepeatEndAwareTrait
{
    /**
     * The timestamp when the repetition ends.
     *
     * @since [*next-version*]
     *
     * @var int|null
     */
    protected $repeatEnd;

    /**
     * Retrieves the timestamp when the repetition ends.
     *
     * @since [*next-version*]
     *
     * @return int|null The number of seconds from the start until the rule repeats, or null for no repetition.
     */
    protected function _getRepeatEnd()
    {
        return $this->repeatEnd;
    }

    /**
     * Sets the timestamp when the repetition ends.
     *
     * @since [*next-version*]
     *
     * @param int|null $repeatEnd The timestamp when the repetition ends; null for no repetition.
     */
    protected function _setRepeatEnd($repeatEnd)
    {
        if ($repeatEnd === null) {
            $this->repeatEnd = $repeatEnd;

            return;
        }

        $this->repeatEnd = $this->_normalizeTimestamp($repeatEnd);
    }

    /**
     * Normalizes a timestamp to an integer number.
     *
     * @since [*next-version*]
     *
     * @param int|float|string|Stringable $timestamp The timestamp.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return int The normalized timestamp.
     */
    abstract protected function _normalizeTimestamp($timestamp);
}
