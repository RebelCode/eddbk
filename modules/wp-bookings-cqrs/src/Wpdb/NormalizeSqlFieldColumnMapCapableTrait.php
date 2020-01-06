<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Functionality for normalizing SQL field-to-column maps, transforming array columns into entity-field instances.
 *
 * @since [*next-version*]
 */
trait NormalizeSqlFieldColumnMapCapableTrait
{
    /**
     * Normalizes an SQL field-to-column map, transforming array columns into entity-field instances.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $map The field-to-column map.
     *
     * @return array The normalized SQL field-to-column map.
     */
    protected function _normalizeSqlFieldColumnMap($map)
    {
        $newMap     = [];
        $expBuilder = $this->_getExprBuilder();

        foreach ($map as $_key => $_value) {
            try {
                $array = $this->_normalizeArray($_value);
            } catch (InvalidArgumentException $exception) {
                $newMap[$_key] = $_value;
                continue;
            }

            if (count($array) === 2) {
                $newMap[$_key] = $expBuilder->ef($array[0], $array[1]);
            }
        }

        return $newMap;
    }

    /**
     * Retrieves the expression builder.
     *
     * @since [*next-version*]
     *
     * @return object The expression builder.
     */
    abstract protected function _getExprBuilder();

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);
}
