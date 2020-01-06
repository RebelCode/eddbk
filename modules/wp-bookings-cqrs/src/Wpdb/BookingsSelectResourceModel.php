<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;
use wpdb;

/**
 * A SELECT resource model specific to bookings.
 *
 * This implementation includes the resources from the relationship table and groups bookings such that a new column is
 * included that contains a comma separated list of resource IDs.
 *
 * @since [*next-version*]
 */
class BookingsSelectResourceModel extends EddBkWpdbSelectResourceModel
{
    /**
     * The name of the resource IDs aggregate column.
     *
     * @since [*next-version*]
     */
    const RESOURCES_COLUMN = 'resource_ids';

    /**
     * The name of the resource IDs field in results.
     *
     * @since [*next-version*]
     */
    const RESOURCES_FIELD = 'resource_ids';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]|EntityFieldInterface[]|stdClass|Traversable $grouping       The fields to group by.
     * @param string|Stringable                                                 $resourcesTable The booking-resources
     *                                                                                          relationship table name.
     */
    public function __construct(
        wpdb $wpdb,
        TemplateInterface $expressionTemplate,
        MapFactoryInterface $factory,
        $tables,
        $fieldColumnMap,
        $resourcesTable,
        $expBuilder,
        $joins = [],
        $grouping = []
    ) {
        $fieldColumnMap[static::RESOURCES_COLUMN] = $expBuilder->fn(
            'GROUP_CONCAT',
            $expBuilder->ef($resourcesTable, 'resource_id')
        );

        parent::__construct(
            $wpdb,
            $expressionTemplate,
            $factory,
            $tables,
            $fieldColumnMap,
            $expBuilder,
            $joins,
            $grouping
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createResult($rawResult)
    {
        // Create array result
        $rawResult = $this->_normalizeArray($rawResult);

        // Get a copy of the resources column value and remove it from the record
        // This ensures that the field added further below is not accidentally removed, should the column name be the
        // same as the field name.
        $resources = $rawResult[static::RESOURCES_COLUMN];
        unset($rawResult[static::RESOURCES_COLUMN]);

        // Add resources field - explode comma list into an array
        $rawResult[static::RESOURCES_FIELD] = explode(',', $resources);

        return parent::_createResult($rawResult);
    }
}
