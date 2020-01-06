<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CreateIterationCapableTrait;
use Dhii\Iterator\CreateIteratorExceptionCapableTrait;
use Dhii\Iterator\IterationAwareTrait;
use Dhii\Iterator\IteratorInterface;
use Dhii\Iterator\IteratorTrait;
use Dhii\Time\PeriodInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Exception as RootException;
use RebelCode\EddBookings\Sessions\Time\CreatePeriodCapableFactoryTrait;
use RebelCode\EddBookings\Sessions\Time\PeriodFactoryAwareTrait;
use RebelCode\EddBookings\Sessions\Time\PeriodFactoryInterface;
use RebelCode\Time\EndAwareTrait;
use RebelCode\Time\NormalizeTimestampCapableTrait;
use RebelCode\Time\StartAwareTrait;
use stdClass;
use Traversable;

/**
 * Abstract functionality for a session generator rule.
 *
 * @since [*next-version*]
 */
abstract class AbstractIteratorRule implements PeriodInterface, IteratorInterface
{
    /* @since [*next-version*] */
    use IteratorTrait;

    /* @since [*next-version*] */
    use IterationAwareTrait;

    /* @since [*next-version*] */
    use StartAwareTrait;

    /* @since [*next-version*] */
    use EndAwareTrait;

    /* @since [*next-version*] */
    use RepeatFreqAwareTrait;

    /* @since [*next-version*] */
    use RepeatEndAwareTrait;

    /* @since [*next-version*] */
    use ExcludedDatesAwareTrait;

    /* @since [*next-version*] */
    use PeriodFactoryAwareTrait;

    /* @since [*next-version*] */
    use CreatePeriodCapableFactoryTrait;

    /* @since [*next-version*] */
    use NormalizeTimestampCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateIterationCapableTrait;

    /* @since [*next-version*] */
    use CreateIteratorExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The current iteration occurrence start timestamp.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $current;

    /**
     * The timestamp at which to stop iteration.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $iterationEnd;

    /**
     * Initializes the session generator rule.
     *
     * @since [*next-version*]
     *
     * @param PeriodFactoryInterface|null $periodFactory The period factory instance, if any.
     * @param int|null                    $start         The start timestamp fo the rule.
     * @param int|null                    $end           The end timestamp fo the rule.
     * @param int|null                    $repeatFreq    How frequently the rule repeats.
     * @param int|null                    $repeatEnd     The timestamp when the repetition ends.
     * @param array|stdClass|Traversable  $excludeDates  The list of dates to exclude.
     */
    protected function _initRule(
        $periodFactory,
        $start,
        $end,
        $repeatFreq = null,
        $repeatEnd = null,
        $excludeDates = []
    ) {
        $this->_setPeriodFactory($periodFactory);
        $this->_setStart($start);
        $this->_setEnd($end);
        $this->_setRepeatFreq($repeatFreq);
        $this->_setRepeatEnd($repeatEnd);
        $this->_setExcludedDates($excludeDates);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getStart()
    {
        return $this->_getStart();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEnd()
    {
        return $this->_getEnd();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getDuration()
    {
        return abs($this->_getEnd() - $this->_getStart());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->_rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        $this->_next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return $this->_valid();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return $this->_value();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->_key();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIteration()
    {
        return $this->_getIteration();
    }

    /**
     * The timestamp at which to stop iteration.
     *
     * @since [*next-version*]
     *
     * @return int
     */
    protected function _getIterationEnd()
    {
        // Calculate if necessary
        if ($this->iterationEnd === null) {
            $ruleEnd   = $this->_getEnd();
            $repeatEnd = $this->_getRepeatEnd();
            // If no repetition, use rule's end timestamp as the end of iteration
            $this->iterationEnd = ($repeatEnd === null) ? $ruleEnd : $repeatEnd;
        }

        return $this->iterationEnd;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _reset()
    {
        // Reset cursor to this rule's period start timestamp
        $this->current = $this->_getStart();

        // Yield this rule as the first period
        return $this->_createIteration($this->current, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _loop()
    {
        // Check if not a repeating rule, and if so, stop
        if (intval($this->_getRepeatFreq()) === 0) {
            return $this->_createIteration(null, null);
        }

        // Retrieves the next occurrence
        $this->current = $this->_getNextOccurrence($this->current);

        // Check if reached end or not
        if ($this->current >= $this->_getIterationEnd()) {
            return $this->_createIteration(null, null);
        }

        // If the occurrence is excluded, recurse to calculate the next one
        if ($this->_isExcluded($this->current)) {
            return $this->_loop();
        }

        // Calculate the end timestamp for this occurrence
        $end = $this->current + $this->getDuration();

        // Create the period instance to yield
        $period = $this->_createPeriod($this->current, $end);

        return $this->_createIteration($this->current, $period);
    }

    /**
     * Checks whether a timestamp is excluded.
     *
     * @since [*next-version*]
     *
     * @param int $timestamp The occurrence to check.
     *
     * @return bool True if excluded, false if not.
     */
    protected function _isExcluded($timestamp)
    {
        foreach ($this->_getExcludedDates() as $_excludeDate) {
            $_excludeStart = $_excludeDate;
            $_excludeEnd   = $_excludeStart + (24 * 60 * 60);

            if ($timestamp >= $_excludeStart && $timestamp < $_excludeEnd) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _throwIteratorException(
        $message = null,
        $code = null,
        RootException $previous = null
    ) {
        throw $this->_createIteratorException($message, $code, $previous, $this);
    }

    /**
     * Retrieves the next occurrence start timestamp.
     *
     * @since [*next-version*]
     *
     * @param int $timestamp The start timestamp from which to get the next occurrence.
     *
     * @return int The start timestamp of the next occurrence.
     */
    abstract protected function _getNextOccurrence($timestamp);
}
