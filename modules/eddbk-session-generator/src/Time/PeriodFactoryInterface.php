<?php

namespace RebelCode\EddBookings\Sessions\Time;

use Dhii\Factory\FactoryInterface;
use Dhii\Time\PeriodInterface;

/**
 * Something that can create period instances.
 *
 * @since [*next-version*]
 */
interface PeriodFactoryInterface extends FactoryInterface
{
    /**
     * The config key for the start timestamp of the period to create.
     *
     * @since [*next-version*]
     */
    const K_START = 'start';

    /**
     * The config key for the end timestamp of the period to create.
     *
     * @since [*next-version*]
     */
    const K_END = 'end';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return PeriodInterface The created period instance.
     */
    public function make($config = null);
}
