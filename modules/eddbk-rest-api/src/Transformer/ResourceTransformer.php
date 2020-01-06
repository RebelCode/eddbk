<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A transformer implementation for transforming resources.
 *
 * @since [*next-version*]
 */
class ResourceTransformer extends MapTransformer
{
    /**
     * The availability transformer to null to exclude availability from transformation results.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $availabilityT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface|null $availabilityT9r The availability transformer or null to exclude availability
     *                                                   from transformation results.
     */
    public function __construct($availabilityT9r = null)
    {
        $this->availabilityT9r = $availabilityT9r;

        parent::__construct($this->_getResourceMapConfig());
    }

    /**
     * Retrieves the map transformation config for resources.
     *
     * @since [*next-version*]
     *
     * @return array The map transformation config.
     */
    protected function _getResourceMapConfig()
    {
        $config = [
            [
                MapTransformer::K_SOURCE => 'id',
            ],
            [
                MapTransformer::K_SOURCE => 'name',
            ],
            [
                MapTransformer::K_SOURCE => 'type',
            ],
            [
                MapTransformer::K_SOURCE => 'data',
            ],
        ];

        if ($this->availabilityT9r !== null) {
            $config[] = [
                MapTransformer::K_SOURCE      => 'availability',
                MapTransformer::K_TRANSFORMER => $this->availabilityT9r,
            ];
        }

        return $config;
    }
}
