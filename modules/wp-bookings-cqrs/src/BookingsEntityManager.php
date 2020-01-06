<?php

namespace RebelCode\Storage\Resource\WordPress;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use stdClass;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * A bookings entity manager implementation.
 *
 * This implementation also manages the booking-resources on booking changes and deletions.
 *
 * @since [*next-version*]
 */
class BookingsEntityManager extends BaseCqrsEntityManager
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The key in booking entity data from where resources are read.
     *
     * @since [*next-version*]
     */
    const K_DATA_RESOURCES = 'resource_ids';

    /**
     * The key in booking-resource records where the booking ID is stored.
     *
     * @since [*next-version*]
     */
    const K_BK_RESOURCES_RECORD_BOOKING_ID = 'booking_id';

    /**
     * The key in booking-resource records where the resource ID is stored.
     *
     * @since [*next-version*]
     */
    const K_BK_RESOURCES_RECORD_RESOURCE_ID = 'resource_id';

    /**
     * The INSERT resource model for booking-resource records.
     *
     * @since [*next-version*]
     *
     * @var InsertCapableInterface
     */
    protected $bkResourcesInsertRm;

    /**
     * The DELETE resource model for booking-resource records.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $bkResourcesDeleteRm;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $selectRm            The SELECT resource model for bookings.
     * @param InsertCapableInterface $insertRm            The INSERT resource model for bookings.
     * @param UpdateCapableInterface $updateRm            The UPDATE resource model for bookings.
     * @param DeleteCapableInterface $deleteRm            The DELETE resource model for bookings.
     * @param InsertCapableInterface $bkResourcesInsertRm The INSERT resource model for booking-resource records.
     * @param DeleteCapableInterface $bkResourcesDeleteRm The DELETE resource model for booking-resource records.
     * @param FactoryInterface       $orderFactory        The factory for creating order object instances.
     * @param object                 $exprBuilder         The expression builder.
     */
    public function __construct(
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        UpdateCapableInterface $updateRm,
        DeleteCapableInterface $deleteRm,
        InsertCapableInterface $bkResourcesInsertRm,
        DeleteCapableInterface $bkResourcesDeleteRm,
        FactoryInterface $orderFactory,
        $exprBuilder
    ) {
        $this->bkResourcesInsertRm = $bkResourcesInsertRm;
        $this->bkResourcesDeleteRm = $bkResourcesDeleteRm;

        parent::__construct($selectRm, $insertRm, $updateRm, $deleteRm, $orderFactory, $exprBuilder);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function add($entity)
    {
        $id = parent::add($entity);

        $this->_addResources($id, $entity);

        return $id;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function delete($id)
    {
        parent::delete($id);

        $this->_deleteResources($id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function update($id, $data)
    {
        parent::update($id, $data);

        if (!$this->_containerHas($data, static::K_DATA_RESOURCES)) {
            return;
        }

        $this->_deleteResources($id);
        $this->_addResources($id, $data);
    }

    /**
     * Adds resources for a booking.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable                         $id     The booking ID.
     * @param array|stdClass|ArrayAccess|ContainerInterface $entity The booking entity.
     */
    protected function _addResources($id, $entity)
    {
        if (!$this->_containerHas($entity, static::K_DATA_RESOURCES)) {
            return;
        }

        // Get the resource IDs from the entity, and create a booking-resource record for each one
        // This serves to "assign" each resource to the booking
        $resourceIds = $this->_containerGet($entity, static::K_DATA_RESOURCES);
        $resourceIds = $this->_normalizeIterable($resourceIds);
        $bkResources = [];
        foreach ($resourceIds as $_resourceId) {
            $bkResources[] = [
                static::K_BK_RESOURCES_RECORD_BOOKING_ID  => $id,
                static::K_BK_RESOURCES_RECORD_RESOURCE_ID => $_resourceId,
            ];
        }

        $this->bkResourcesInsertRm->insert($bkResources);
    }

    /**
     * Deletes the assigned resources for a booking.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $id The booking ID.
     */
    protected function _deleteResources($id)
    {
        $b = $this->exprBuilder;

        $this->bkResourcesDeleteRm->delete(
            $b->eq(
                $b->var(static::K_BK_RESOURCES_RECORD_BOOKING_ID),
                $b->lit($id)
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createIdExpression($id)
    {
        return $this->exprBuilder->eq(
            $this->exprBuilder->var('id'),
            $this->exprBuilder->lit($id)
        );
    }
}
