<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use ArrayIterator;
use Carbon\Carbon;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Iterator;
use IteratorIterator;
use RebelCode\EddBookings\Sessions\Time\PeriodFactoryInterface;
use stdClass;
use Traversable;

/**
 * A session generator rule that repeats in two ways: every week and on certain week days.
 *
 * @since [*next-version*]
 */
class WeeklyRepeatingRule extends AbstractIteratorRule
{
    /**
     * Constructor.
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
    public function __construct(
        $periodFactory,
        $start,
        $end,
        $repeatFreq = null,
        $repeatEnd = null,
        $excludeDates = []
    ) {
        $this->_initRule($periodFactory, $start, $end, $repeatFreq, $repeatEnd, $excludeDates);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getNextOccurrence($timestamp)
    {
        $current = Carbon::createFromTimestampUTC($timestamp);
        $current->addWeeks($this->_getRepeatFreq());

        return $current->getTimestamp();
    }
}
