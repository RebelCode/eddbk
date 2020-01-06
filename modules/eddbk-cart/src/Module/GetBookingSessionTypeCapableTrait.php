<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Cache\ContainerInterface as CacheContainerInterface;
use Dhii\Data\StateAwareInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Entity\GetCapableManagerInterface;
use RuntimeException;
use stdClass;
use Traversable;

/**
 * Functionality for retrieving the session type that matches a specific booking.
 *
 * @since [*next-version*]
 */
trait GetBookingSessionTypeCapableTrait
{
    /**
     * Retrieves the session type that matches a given booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking. Must also be state-aware.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The session type data container.
     */
    protected function _getBookingSessionType($booking)
    {
        $bookingDuration  = $booking->getDuration();
        $bookingResources = $this->_normalizeArray($booking->getResourceIds());

        try {
            $serviceId = $booking->getState()->get('service_id');
            $service   = $this->_getServiceCache()->get($serviceId, function ($id) {
                return $this->_getServicesManager()->get($id);
            });
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createRuntimeException(
                $this->__('Cannot determine booking price - the booked service does not exist'), null, $exception
            );
        }

        // Disregard the schedule resource ID in the booking
        $scheduleId       = $this->_containerGet($service, 'schedule_id');
        $bookingResources = array_diff($bookingResources, [$scheduleId]);

        $sessionTypes = $this->_containerGet($service, 'session_types');

        foreach ($sessionTypes as $_sessionType) {
            $_data      = $this->_containerGet($_sessionType, 'data');
            $_duration  = (int) $this->_containerGet($_data, 'duration');
            try {
                $_resources = $this->_containerGet($_data, 'resources');
            } catch (NotFoundExceptionInterface $exception) {
                $_resources = [];
            }

            // A fast way to check if a booking matches a session type in terms of resources is to intersect their
            // resources. If the resulting list is not empty, than the session type and the booking share at least
            // one resource. There is one exception: when the session type does not have any resources.
            $_rIntersect = array_intersect($_resources, $bookingResources);

            if ($bookingDuration === $_duration && (!empty($_rIntersect) || empty($_resources))) {
                return $_sessionType;
            }
        }

        throw $this->_createRuntimeException(
            $this->__('Cannot determine booking price - booking does not match any session type'), null, null
        );
    }

    /**
     * Retrieves the services manager.
     *
     * @since [*next-version*]
     *
     * @return GetCapableManagerInterface The services manager.
     */
    abstract protected function _getServicesManager();

    /**
     * Retrieves the cache for services.
     *
     * @since [*next-version*]
     *
     * @return CacheContainerInterface The services cache.
     */
    abstract protected function _getServiceCache();

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

    /**
     * Creates a new Runtime exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param Exception|null                        $previous The inner exception, if any.
     *
     * @return RuntimeException The new exception.
     */
    abstract protected function _createRuntimeException($message = null, $code = null, $previous = null);

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     * @see   _translate()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
