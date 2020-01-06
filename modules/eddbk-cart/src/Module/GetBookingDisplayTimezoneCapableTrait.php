<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use DateTimeZone;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Time\CreateDateTimeZoneCapableTrait;
use stdClass;

/**
 * Common functionality for retrieving a booking's display timezone.
 *
 * @since [*next-version*]
 */
trait GetBookingDisplayTimezoneCapableTrait
{
    /* @since [*next-version*] */
    use CreateDateTimeZoneCapableTrait;

    /**
     * The fallback timezone.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable|null
     */
    protected $fallbackTz;

    /**
     * Retrieves the fallback timezone.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable|null
     */
    protected function _getFallbackTz()
    {
        return $this->fallbackTz;
    }

    /**
     * Sets the fallback timezone.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $fallbackTz
     */
    protected function _setFallbackTz($fallbackTz)
    {
        if (!is_null($fallbackTz) && !is_string($fallbackTz) && !($fallbackTz instanceof Stringable)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a string or stringable object'), null, null, $fallbackTz
            );
        }

        $this->fallbackTz = $fallbackTz;
    }

    /**
     * Retrieves the timezone to use for displaying booking dates and times.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $bookingData The booking data.
     *
     * @return DateTimeZone The timezone instance.
     *
     * @throws OutOfRangeException If one of the attempted timezones is invalid.
     */
    protected function _getDisplayTimezone($bookingData)
    {
        $try = [
            [$this, '_getBookingClientTimezone'],
            [$this, '_getFallbackTimezone'],
            [$this, '_getWordPressTimezone'],
        ];

        // Try all, until a non-null timezone is retrieved
        foreach ($try as $_callable) {
            $_tz = call_user_func_array($_callable, [$bookingData]);

            if ($_tz !== null) {
                return $_tz;
            }
        }

        // Final fallback
        return $this->_getServerTimezone();
    }

    /**
     * Retrieves the booking's client timezone.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $bookingData The booking data.
     *
     * @return DateTimeZone|null The timezone instance, or null if the booking does not have a client timezone set.
     *
     * @throws OutOfRangeException If the booking client's timezone is invalid.
     */
    protected function _getBookingClientTimezone($bookingData)
    {
        $clientTz = $this->_containerHas($bookingData, 'client_tz')
            ? $this->_containerGet($bookingData, 'client_tz')
            : null;

        if (strlen($clientTz) == 0) {
            return null;
        }

        $clientTz = $this->_containerGet($bookingData, 'client_tz');
        $timezone = $this->_createDateTimeZone($clientTz);

        return $timezone;
    }

    /**
     * Retrieves the fallback timezone.
     *
     * @since [*next-version*]
     *
     * @return DateTimeZone|null The timezone instance, or null if no fallback timezone is set.
     *
     * @throws OutOfRangeException If the fallback timezone is invalid.
     */
    protected function _getFallbackTimezone()
    {
        $fallbackTz = $this->_normalizeString($this->fallbackTz);

        if (strlen($fallbackTz) == 0) {
            return null;
        }

        return $this->_createDateTimeZone($fallbackTz);
    }

    /**
     * Retrieves the WordPress timezone.
     *
     * @since [*next-version*]
     *
     * @return DateTimeZone|null The timezone instance, or null if the WordPress timezone is not set.
     *
     * @throws OutOfRangeException If the WordPress timezonoe is invalid.
     */
    protected function _getWordPressTimezone()
    {
        $wpTimezone = $this->_getWordPressOption('timezone_string', '');
        // Return the timezone with this name if not empty
        if (strlen($wpTimezone) > 0) {
            return $this->_createDateTimeZone($wpTimezone);
        }

        // Get GMT offset as a fallback
        $gmtOffset = (float) $this->_getWordPressOption('gmt_offset', '');
        // Return null if empty
        if (strlen($gmtOffset) == 0) {
            return null;
        }

        // Ensure the sign is attached to the offset
        $wpTimezone = sprintf('%+f', floatval($gmtOffset));

        return $this->_createDateTimeZone($wpTimezone);
    }

    /**
     * Retrieves the server timezone.
     *
     * @since [*next-version*]
     *
     * @return DateTimeZone|null The timezone instance, or null if no server timezone is set.
     *
     * @throws OutOfRangeException If the server timezone is invalid.
     */
    protected function _getServerTimezone()
    {
        $serverTz = date_default_timezone_get();

        if (strlen($serverTz) == 0) {
            return null;
        }

        return $this->_createDateTimeZone($serverTz);
    }

    /**
     * Retrieves the value for a wordpress option.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key     The key of the option.
     * @param bool              $default The default value to return if the option with the given $key is not found.
     *
     * @return mixed|null The value of the option, or the value of the $default parameter if the option was not found.
     */
    protected function _getWordPressOption($key, $default = false)
    {
        return \get_option($this->_normalizeString($key), $default);
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable                  $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Checks for a key on a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to check.
     * @param string|int|float|bool|Stringable                  $key       The key to check for.
     *
     * @throws ContainerExceptionInterface If an error occurred while checking the container.
     * @throws OutOfRangeException         If the container or the key is invalid.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    abstract protected function _containerHas($container, $key);

    /**
     * Normalizes a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to normalize.
     *
     * @throws InvalidArgumentException If the container is invalid.
     *
     * @return array|ArrayAccess|stdClass|BaseContainerInterface Something that can be used with
     *                                                           {@see ContainerGetCapableTrait#_containerGet()} or
     *                                                           {@see ContainerHasCapableTrait#_containerHas()}.
     */
    abstract protected function _normalizeContainer($container);

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Creates a new Dhii Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

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
