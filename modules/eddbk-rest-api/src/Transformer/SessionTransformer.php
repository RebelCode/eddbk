<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A transformer implementation for transforming sessions.
 *
 * @since [*next-version*]
 */
class SessionTransformer extends MapTransformer
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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $tsDatetimeT9r The timestamp to datetime transformer.
     */
    public function __construct(TransformerInterface $tsDatetimeT9r)
    {
        $this->tsDatetimeT9r = $tsDatetimeT9r;

        parent::__construct($this->_getSessionMapConfig());
    }

    /**
     * Retrieves the map transformation config for sessions.
     *
     * @since [*next-version*]
     *
     * @return array The map transformation config.
     */
    protected function _getSessionMapConfig()
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
                MapTransformer::K_SOURCE => 'service_id',
                MapTransformer::K_TARGET => 'service',
            ],
            [
                MapTransformer::K_SOURCE      => 'resource_ids',
                MapTransformer::K_TARGET      => 'resources',
                MapTransformer::K_TRANSFORMER => function ($resourceIds) {
                    return $this->_normalizeArray($resourceIds);
                },
            ],
        ];
    }
}
