<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A services transformer that transformers services with only their core information.
 *
 * @since [*next-version*]
 */
class CoreInfoServiceTransformer extends MapTransformer
{
    /**
     * The transformer for session type lists.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $sessionTypeListT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $sessionTypeListT9r The transformer for session type lists.
     */
    public function __construct(TransformerInterface $sessionTypeListT9r)
    {
        $this->sessionTypeListT9r = $sessionTypeListT9r;

        parent::__construct($this->_getServiceMapConfig());
    }

    /**
     * Retrieves the map config for this service transformer.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getServiceMapConfig()
    {
        return [
            [
                MapTransformer::K_SOURCE => 'id',
                MapTransformer::K_TARGET => 'id',
            ],
            [
                MapTransformer::K_SOURCE => 'name',
                MapTransformer::K_TARGET => 'name',
            ],
            [
                MapTransformer::K_SOURCE => 'description',
                MapTransformer::K_TARGET => 'description',
            ],
            [
                MapTransformer::K_SOURCE => 'image_url',
                MapTransformer::K_TARGET => 'imageSrc',
            ],
            [
                MapTransformer::K_SOURCE => 'session_types',
                MapTransformer::K_TARGET => 'sessionTypes',
                MapTransformer::K_TRANSFORMER => $this->sessionTypeListT9r,
            ],
            [
                MapTransformer::K_SOURCE => 'timezone',
            ],
            [
                MapTransformer::K_SOURCE => 'display_options',
                MapTransformer::K_TARGET => 'displayOptions',
            ],
        ];
    }
}
