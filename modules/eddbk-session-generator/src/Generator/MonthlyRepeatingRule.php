<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Carbon\Carbon;
use Dhii\Time\PeriodInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\EddBookings\Sessions\Time\PeriodFactoryInterface;
use stdClass;
use Traversable;

/**
 * Implementation of a rule that repeats monthly.
 *
 * @since [*next-version*]
 */
class MonthlyRepeatingRule extends AbstractIteratorRule
{
    /**
     * The mode for repeating monthly using the date number.
     *
     * @since [*next-version*]
     */
    const MODE_DATE_OF_MONTH = 'day_of_month';

    /**
     * The mode for repeating monthly on the Nth day of the week.
     *
     * @since [*next-version*]
     */
    const MODE_DAY_OF_WEEK = 'day_of_week';

    /**
     * The month repetition mode.
     *
     * @since [*next-version*]
     *
     * @see   MonthlyRepeatingRule::MODE_DATE_OF_MONTH
     * @see   MonthlyRepeatingRule::MODE_DAY_OF_WEEK
     *
     * @var string
     */
    protected $monthRepeatMode;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PeriodFactoryInterface|null $periodFactory   The period factory instance, if any.
     * @param int|null                    $start           The start timestamp fo the rule.
     * @param int|null                    $end             The end timestamp fo the rule.
     * @param int|null                    $repeatFreq      How frequently the rule repeats.
     * @param int|null                    $repeatEnd       The timestamp when the repetition ends.
     * @param string|Stringable           $monthRepeatMode The monthly repeating mode. See constants.
     * @param array|stdClass|Traversable  $excludeDates    The list of dates to exclude.
     */
    public function __construct(
        $periodFactory,
        $start,
        $end,
        $repeatFreq = null,
        $repeatEnd = null,
        $monthRepeatMode = self::MODE_DATE_OF_MONTH,
        $excludeDates = []
    ) {
        $this->_initRule($periodFactory, $start, $end, $repeatFreq, $repeatEnd, $excludeDates);
        $this->_setMonthRepeatMode($monthRepeatMode);
    }

    /**
     * Retrieves the monthly repeating mode.
     *
     * @since [*next-version*]
     *
     * @see   MonthlyRepeatingRule::MODE_DATE_OF_MONTH
     * @see   MonthlyRepeatingRule::MODE_DAY_OF_WEEK
     *
     * @return string The monthly repeating mode. See constants.
     */
    protected function _getMonthRepeatMode()
    {
        return $this->monthRepeatMode;
    }

    /**
     * Sets the monthly repeating mode.
     *
     * @since [*next-version*]
     *
     * @see   MonthlyRepeatingRule::MODE_DATE_OF_MONTH
     * @see   MonthlyRepeatingRule::MODE_DAY_OF_WEEK
     *
     * @param string|Stringable $monthRepeatMode The monthly repeating mode. See constants.
     */
    protected function _setMonthRepeatMode($monthRepeatMode)
    {
        $this->monthRepeatMode = $this->_normalizeString($monthRepeatMode);
    }

    /**
     * Retrieves the next occurrence period from a given start timestamp.
     *
     * @since [*next-version*]
     *
     * @param int $timestamp The start timestamp from which to get the next occurrence.
     *
     * @return PeriodInterface The next occurrence period.
     */
    protected function _getNextOccurrence($timestamp)
    {
        $datetime = Carbon::createFromTimestampUTC($timestamp);

        switch ($this->monthRepeatMode) {
            case static::MODE_DATE_OF_MONTH:
                $datetime->addMonths($this->_getRepeatFreq());
                break;

            case static::MODE_DAY_OF_WEEK:
                // Get the day-of-the-week of the current time
                $dotw = $datetime->dayOfWeek;
                // Get the time only
                $time = $datetime->toTimeString();
                // Get the nth month index of the current time
                $nthOfMonth = $datetime->weekOfMonth;

                // Keep trying to retrieve the next nth day of the month until successful
                /* @var $result Carbon|false */
                $result = false;
                while (!$result) {
                    // Add months
                    $datetime->addMonths($this->_getRepeatFreq());
                    // Get next nth day of the month
                    $result = $datetime->nthOfMonth($nthOfMonth, $dotw);
                }

                $datetime = $result->setTimeFromTimeString($time);;
                break;
        }

        return $datetime->getTimestamp();
    }
}
