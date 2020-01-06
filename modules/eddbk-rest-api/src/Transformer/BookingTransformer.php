<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use InvalidArgumentException;
use RebelCode\Transformers\MapTransformer;
use stdClass;
use Traversable;

/**
 * A transformer implementation for transforming bookings.
 */
class BookingTransformer extends MapTransformer
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
     * The transformer for transforming service IDs into service data.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $serviceIdT9r;

    /**
     * The transformer for transforming resource IDs into resource data.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $resourceIdT9r;

    /**
     * The transformer for transforming client IDs into client data.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $clientIdT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $tsDatetimeT9r The transformer for transforming timestamps to datetime strings.
     * @param TransformerInterface $serviceIdT9r  The transformer for transforming service IDs into service data.
     * @param TransformerInterface $resourceIdT9r The transformer for transforming resource IDs into resource data.
     * @param TransformerInterface $clientIdT9r   The transformer for transforming client IDs into client data.
     */
    public function __construct($tsDatetimeT9r, $serviceIdT9r, $resourceIdT9r, $clientIdT9r)
    {
        $this->tsDatetimeT9r = $tsDatetimeT9r;
        $this->serviceIdT9r  = $serviceIdT9r;
        $this->resourceIdT9r = $resourceIdT9r;
        $this->clientIdT9r   = $clientIdT9r;

        parent::__construct($this->_getBookingMapConfig());
    }

    /**
     * Retrieves the map transformation config for bookings.
     *
     * @since [*next-version*]
     *
     * @return array The map transformation config.
     */
    protected function _getBookingMapConfig()
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
                MapTransformer::K_SOURCE => 'status',
            ],
            [
                MapTransformer::K_SOURCE      => 'service_id',
                MapTransformer::K_TARGET      => 'service',
                MapTransformer::K_TRANSFORMER => $this->serviceIdT9r,
            ],
            [
                MapTransformer::K_SOURCE      => 'resource_ids',
                MapTransformer::K_TARGET      => 'resources',
                MapTransformer::K_TRANSFORMER => function ($source) {
                    return $this->_transformResourceIds($source);
                },
            ],
            [
                MapTransformer::K_SOURCE      => 'client_id',
                MapTransformer::K_TARGET      => 'client',
                MapTransformer::K_TRANSFORMER => $this->clientIdT9r,
            ],
            [
                MapTransformer::K_SOURCE => 'client_tz',
                MapTransformer::K_TARGET => 'clientTzName',
            ],
            [
                MapTransformer::K_SOURCE => 'payment_id',
                MapTransformer::K_TARGET => 'payment',
            ],
            [
                MapTransformer::K_SOURCE => 'admin_notes',
                MapTransformer::K_TARGET => 'notes',
            ],
        ];
    }

    /**
     * Transforms a list of resource IDs into a list containing full resource data for each ID.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $source The list of resource IDs.
     *
     * @return array|stdClass|Traversable A list of resources.
     */
    protected function _transformResourceIds($source)
    {
        try {
            $resources = $this->_normalizeIterable($source);
        } catch (InvalidArgumentException $exception) {
            return [];
        }

        $result = [];

        foreach ($resources as $_key => $resourceId) {
            $_transformed = $this->resourceIdT9r->transform($resourceId);
            if ($_transformed !== null) {
                $result[] = $_transformed;
            }
        }

        return $result;
    }
}
