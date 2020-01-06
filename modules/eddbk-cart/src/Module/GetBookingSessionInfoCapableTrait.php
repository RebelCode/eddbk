<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Cache\ContainerInterface as CacheContainerInterface;
use Dhii\Data\StateAwareInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Entity\GetCapableManagerInterface;
use stdClass;
use Traversable;

trait GetBookingSessionInfoCapableTrait
{
    protected function _getBookingSessionInfo($booking)
    {
        $sessionType   = $this->_getBookingSessionType($booking);
        $resourceNames = $this->_getBookingResourceNames($booking);

        return [
            'session_label'  => $this->_containerGet($sessionType, 'label'),
            'resource_names' => $resourceNames,
        ];
    }

    /**
     * Retrieves the names of the resource assigned to a specific booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface $booking The booking.
     *
     * @return string[]|Stringable[]|stdClass|Traversable The list of resource names.
     */
    protected function _getBookingResourceNames($booking)
    {
        $ids     = $booking->getResourceIds();
        $manager = $this->_getResourcesManager();
        $cache   = $this->_getResourceCache();
        $names   = [];

        foreach ($ids as $_resourceId) {
            $_resource = $cache->get($_resourceId, function ($id) use ($manager) {
                $resource = $manager->get($id);

                return ($this->_containerGet($resource, 'type') === 'schedule')
                    ? null
                    : $resource;
            });

            if ($_resource !== null) {
                $names[] = $this->_containerGet($_resource, 'name');
            }
        }

        return $names;
    }

    /**
     * Retrieves the resources manager.
     *
     * @since [*next-version*]
     *
     * @return GetCapableManagerInterface The resources manager.
     */
    abstract protected function _getResourcesManager();

    /**
     * Retrieves the cache for resources.
     *
     * @since [*next-version*]
     *
     * @return CacheContainerInterface The resources cache.
     */
    abstract protected function _getResourceCache();

    /**
     * Retrieves the session type that matches a given booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking. Must also be state-aware.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The session type data container.
     */
    abstract protected function _getBookingSessionType($booking);

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);
}
