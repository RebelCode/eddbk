<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Factory\FactoryInterface;
use Iterator;
use Traversable;

/**
 * Common functionality for creating result iterators using a factory.
 *
 * @since [*next-version*]
 */
trait CreateResultsIteratorCapableFactoryTrait
{
    /**
     * Creates the iterator for the results.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $results The results.
     *
     * @return Iterator The results iterator.
     */
    protected function _createResultsIterator($results)
    {
        return $this->_getResultsIteratorFactory($results)->make([
            'items' => $results,
        ]);
    }

    /**
     * Retrieves the results iterator factory to use.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $results The results, for which to get the iterator factory.
     *
     * @return FactoryInterface The factory instance.
     */
    abstract protected function _getResultsIteratorFactory($results);
}
