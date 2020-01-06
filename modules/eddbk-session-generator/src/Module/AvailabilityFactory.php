<?php

namespace RebelCode\EddBookings\Sessions\Module;

use Carbon\Carbon;
use DateTime;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateCouldNotMakeExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use RebelCode\Bookings\Availability\CompositeAvailability;
use RebelCode\Bookings\Availability\DailyRepeatingAvailability;
use RebelCode\Bookings\Availability\DotwWeeklyRepeatingAvailability;
use RebelCode\Bookings\Availability\MonthlyDateRepeatingAvailability;
use RebelCode\Bookings\Availability\MonthlyWeekDayRepeatingAvailability;
use RebelCode\Bookings\Availability\SubtractiveAvailability;
use RebelCode\Bookings\Availability\WeeklyRepeatingAvailability;
use RebelCode\Bookings\Util\Time\Period;
use RebelCode\Time\CreateDateTimeZoneCapableTrait;

/**
 * Factory implementation that creates availability rules based on their repetition unit.
 *
 * @since [*next-version*]
 */
class AvailabilityFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateDateTimeZoneCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotMakeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The duration up till which the weekly day-of-the-week repetition processing applies.
     *
     * @since [*next-version*]
     */
    const WEEKLY_REPEAT_DOTW_DURATION_THRESHOLD = 86400;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $start       = (int) $this->_containerGet($config, 'start');
        $end         = (int) $this->_containerGet($config, 'end');
        $resourceIds = $this->_containerGet($config, 'resource_ids');
        $timezone    = $this->_createDateTimeZone($this->_containerGet($config, 'timezone'));
        $firstPeriod = $firstPeriod = new Period($start, $end);;

        $repeat       = (bool) $this->_containerGet($config, 'repeat');
        $excludeDates = $this->_containerGet($config, 'exclude_dates');
        $excludeDates = array_filter(explode(',', $excludeDates));

        if (!$repeat) {
            return new DailyRepeatingAvailability(
                new Period($start, $end),
                1,
                $end,
                $timezone,
                $resourceIds
            );
        }

        // Get the repetition mode and unit
        $rMode = $this->_containerGet($config, 'repeat_until');
        $rUnit = strtolower($this->_containerGet($config, 'repeat_unit'));
        // Get the repetition period count
        $rUntilPeriod = (int) $this->_containerGet($config, 'repeat_until_period');
        $rUntilPeriod = ($rUntilPeriod < 1) ? 1 : $rUntilPeriod;
        // Get the absolute repetition end date
        $rUntilDate = $this->_containerGet($config, 'repeat_until_date');
        // Calculate repetition end date time
        $rEndDate = ($rMode === 'period')
            // Add it to the start to get the repetition end date
            ? Carbon::createFromTimestampUTC($start)->modify(sprintf('+%1$d %2$s', $rUntilPeriod, $rUnit))
            // Add a day to to the repetition end date to include it in the availability
            : Carbon::createFromTimestampUTC($rUntilDate)->addDay(1);

        // Get the timestamp for the repetition end date
        $rEndTs = $rEndDate->getTimestamp();
        // Get the repetition frequency
        $rFrequency = (int) $this->_containerGet($config, 'repeat_period');

        switch ($rUnit) {
            case 'days':
                $availability = new DailyRepeatingAvailability(
                    $firstPeriod,
                    $rFrequency,
                    $rEndTs,
                    $timezone,
                    $resourceIds
                );
                break;

            case 'weeks':
                $duration = $end - $start;

                if ($duration > static::WEEKLY_REPEAT_DOTW_DURATION_THRESHOLD) {
                    $availability = new WeeklyRepeatingAvailability(
                        $firstPeriod,
                        $rFrequency,
                        $rEndTs,
                        $timezone,
                        $resourceIds
                    );
                    break;
                }

                $dotwNames = $this->_containerGet($config, 'repeat_weekly_on');
                $dotwNames = array_filter(explode(',', $dotwNames));

                $availability = new DotwWeeklyRepeatingAvailability(
                    $firstPeriod,
                    $dotwNames,
                    $rFrequency,
                    $rEndTs,
                    $timezone,
                    $resourceIds
                );
                break;

            case 'months':
                $rMonthlyMode = $this->_containerGet($config, 'repeat_monthly_on');

                if ($rMonthlyMode === 'day_of_month') {
                    $availability = new MonthlyDateRepeatingAvailability(
                        $firstPeriod,
                        $rFrequency,
                        $rEndTs,
                        $timezone,
                        $resourceIds
                    );
                    break;
                }

                $availability = new MonthlyWeekDayRepeatingAvailability(
                    $firstPeriod,
                    $rFrequency,
                    $rEndTs,
                    $timezone,
                    $resourceIds
                );
                break;

            default:
                throw $this->_createCouldNotMakeException(
                    $this->__('The rule config has an invalid repeat unit: "%s"', [$rUnit]),
                    null,
                    null,
                    $this,
                    $config
                );
        }

        if (count($excludeDates) === 0) {
            return $availability;
        }

        // Iterate through the exclude dates to create an availability for each
        $exclusions = [];
        foreach ($excludeDates as $_excludeDate) {
            // Get the date time for the exclude date using the timezone, and calculate the start and end of the date
            $_dateTime = new DateTime('@' . $_excludeDate, $timezone);
            $_dtStart  = (clone $_dateTime)->modify('midnight')->getTimestamp();
            $_dtEnd    = (clone $_dateTime)->modify('tomorrow midnight')->getTimestamp();
            // Create a single-day availability
            $exclusions[] = new DailyRepeatingAvailability(
                new Period($_dtStart, $_dtEnd),
                1,
                $_dtEnd,
                $timezone,
                $resourceIds
            );
        }
        // Create a composite availability for all of the exclusion availabilities
        $exclusionAvailability = new CompositeAvailability($exclusions);

        // Create and return a subtractive availability, so that the rule excludes all periods that lie within the
        // exclude date periods.
        return new SubtractiveAvailability([$availability, $exclusionAvailability]);
    }
}
