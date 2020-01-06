<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

/**
 * A SELECT resource model specific to sessions.
 *
 * This implementation transforms the comma separated list of resource IDs into an array when creating the result set.
 *
 * @since [*next-version*]
 */
class SessionsSelectResourceModel extends EddBkWpdbSelectResourceModel
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
     */
    protected function _createResult($rawResult)
    {
        // Create array result
        $rawResult = $this->_normalizeArray($rawResult);
        // Get the resources column value and remove it from the record
        $resources = $rawResult[static::RESOURCES_COLUMN];
        unset($rawResult[static::RESOURCES_COLUMN]);

        // Add resources field - explode comma list into an array
        $rawResult[static::RESOURCES_FIELD] = explode(',', $resources);

        return parent::_createResult($rawResult);
    }
}
