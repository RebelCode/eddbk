<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A transformer for transforming an availability rule.
 *
 * @since [*next-version*]
 */
class AvailabilityRuleTransformer extends MapTransformer
{
    /**
     * The transformer for transforming timestamps to datetime strings.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $tsDatetimeT9r;

    /**
     * The transformer for transforming values into booleans.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $boolT9r;

    /**
     * The transformer for transforming comma-separated lists into arrays.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $commaListArrayT9r;

    /**
     * The transformer for transforming exclusion dates.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $excludeDatesT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $tsDatetimeT9r
     * @param TransformerInterface $boolT9r
     * @param TransformerInterface $commaListT9r
     * @param TransformerInterface $excludeDatesT9r
     */
    public function __construct(
        TransformerInterface $tsDatetimeT9r,
        TransformerInterface $boolT9r,
        TransformerInterface $commaListT9r,
        TransformerInterface $excludeDatesT9r
    ) {
        $this->tsDatetimeT9r     = $tsDatetimeT9r;
        $this->boolT9r           = $boolT9r;
        $this->commaListArrayT9r = $commaListT9r;
        $this->excludeDatesT9r   = $excludeDatesT9r;

        parent::__construct($this->_getAvailabilityRuleMapConfig());
    }

    /**
     * Retrieves the map config for this availability rule transformer.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getAvailabilityRuleMapConfig()
    {
        return [
            [
                MapTransformer::K_SOURCE => 'id',
            ],
            [
                MapTransformer::K_SOURCE      => 'start',
                MapTransformer::K_TRANSFORMER => $this->tsDatetimeT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'end',
                MapTransformer::K_TRANSFORMER => $this->tsDatetimeT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'all_day',
                MapTransformer::K_TARGET      => 'isAllDay',
                MapTransformer::K_TRANSFORMER => $this->boolT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'repeat',
                MapTransformer::K_TRANSFORMER => $this->boolT9r,
            ],
            [
                MapTransformer::K_SOURCE => 'repeat_period',
                MapTransformer::K_TARGET => 'repeatPeriod',
            ],
            [
                MapTransformer::K_SOURCE => 'repeat_unit',
                MapTransformer::K_TARGET => 'repeatUnit',
            ],
            [
                MapTransformer::K_SOURCE => 'repeat_until',
                MapTransformer::K_TARGET => 'repeatUntil',
            ],
            [
                MapTransformer::K_SOURCE => 'repeat_until_period',
                MapTransformer::K_TARGET => 'repeatUntilPeriod',
            ],
            [
                MapTransformer::K_SOURCE      => 'repeat_until_date',
                MapTransformer::K_TARGET      => 'repeatUntilDate',
                MapTransformer::K_TRANSFORMER => $this->tsDatetimeT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'repeat_weekly_on',
                MapTransformer::K_TARGET      => 'repeatWeeklyOn',
                MapTransformer::K_TRANSFORMER => $this->commaListArrayT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'repeat_monthly_on',
                MapTransformer::K_TARGET      => 'repeatMonthlyOn',
                MapTransformer::K_TRANSFORMER => $this->commaListArrayT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'exclude_dates',
                MapTransformer::K_TARGET      => 'excludeDates',
                MapTransformer::K_TRANSFORMER => $this->excludeDatesT9r,
            ],
        ];
    }
}
