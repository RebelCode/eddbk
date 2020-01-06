<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Carbon\Carbon;
use RebelCode\EddBookings\Sessions\Time\PeriodFactoryInterface;
use stdClass;
use Traversable;

/**
 * Implementation of a session generator rule that repeats daily.
 *
 * @since [*next-version*]
 */
class DailyRepeatingRule extends AbstractIteratorRule
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
        $current->addDays($this->_getRepeatFreq());

        return $current->getTimestamp();
    }
}
