<?php

namespace RebelCode\EddBookings\RestApi\Controller;

/**
 * Provides functions for parsing ISO 8601 date time strings to unix timestamps.
 *
 * @since [*next-version*]
 */
trait ParseIso8601CapableTrait
{
    /**
     * Parses an ISO 8601 date time string to a unix timestamp.
     *
     * @since [*next-version*]
     *
     * @param string|null $iso8601 The ISO 8601 date time string.
     *
     * @return int|false|null The timestamp, or false if the param string was incorrect or null if the param was null.
     */
    protected function _parseIso8601($iso8601)
    {
        return ($iso8601 === null) ? null : strtotime($iso8601);
    }
}
