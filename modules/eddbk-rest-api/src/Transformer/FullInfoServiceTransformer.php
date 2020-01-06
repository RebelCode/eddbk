<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A services transformer that transformers services with all of their information.
 *
 * @since [*next-version*]
 */
class FullInfoServiceTransformer extends CoreInfoServiceTransformer
{
    /**
     * The transformer for availabilities.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $availabilityT9r;

    /**
     * The transformer for transforming values into booleans.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $boolT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $sessionTypeListT9r The transformer for session type lists.
     * @param TransformerInterface $availabilityT9r    The transformer for availabilities.
     * @param TransformerInterface $boolT9r            The transformer for transforming values into booleans.
     */
    public function __construct(
        TransformerInterface $sessionTypeListT9r,
        TransformerInterface $availabilityT9r,
        TransformerInterface $boolT9r
    ) {
        $this->availabilityT9r = $availabilityT9r;
        $this->boolT9r         = $boolT9r;

        parent::__construct($sessionTypeListT9r);
    }

    /**
     * Retrieves the map config for this services transformer.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getServiceMapConfig()
    {
        $config = parent::_getServiceMapConfig();

        $config[] = [
            MapTransformer::K_SOURCE => 'status',
        ];
        $config[] = [
            MapTransformer::K_SOURCE => 'image_id',
            MapTransformer::K_TARGET => 'imageId',
        ];
        $config[] = [
            MapTransformer::K_SOURCE => 'color',
            MapTransformer::K_TARGET => 'color',
        ];
        $config[] = [
            MapTransformer::K_SOURCE      => 'bookings_enabled',
            MapTransformer::K_TARGET      => 'bookingsEnabled',
            MapTransformer::K_TRANSFORMER => $this->boolT9r,
        ];
        $config[] = [
            MapTransformer::K_SOURCE      => 'availability',
            MapTransformer::K_TARGET      => 'availability',
            MapTransformer::K_TRANSFORMER => $this->availabilityT9r,
        ];

        return $config;
    }
}
