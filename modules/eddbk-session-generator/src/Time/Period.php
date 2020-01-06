<?php

namespace RebelCode\EddBookings\Sessions\Time;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Time\PeriodInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use RebelCode\Time\AbstractPeriod;
use RebelCode\Time\EndAwareTrait;
use RebelCode\Time\NormalizeTimestampCapableTrait;
use RebelCode\Time\StartAwareTrait;

/**
 * Implementation of a period of time.
 *
 * @since [*next-version*]
 */
class Period extends AbstractPeriod implements PeriodInterface
{
    /* @since [*next-version*] */
    use StartAwareTrait;

    /* @since [*next-version*] */
    use EndAwareTrait;

    /* @since [*next-version*] */
    use NormalizeTimestampCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int $start The start timestamp, as the number of seconds since unix epoch.
     * @param int $end   The end timestamp, as the number of seconds since unix epoch.
     */
    public function __construct($start, $end)
    {
        $this->_setStart($start);
        $this->_setEnd($end);
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
        return $this->_getDuration();
    }
}
