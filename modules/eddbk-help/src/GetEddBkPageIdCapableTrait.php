<?php

namespace RebelCode\EddBookings\Help;

use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Functionality for retrieving the current EDD Bookings page ID.
 *
 * @since [*next-version*]
 */
trait GetEddBkPageIdCapableTrait
{
    /**
     * Checks if current page is an EDD Bookings admin page, and if so retrieves its ID.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable|null The ID of the EDD Bookings admin page, or null if not an EDD Bookings admin page.
     */
    protected function _getEddBkPageId()
    {
        if (!$this->_wpIsAdmin()) {
            return null;
        }

        $screen = $this->_wpGetCurrentScreen();

        if ($screen === null) {
            return null;
        }

        // Get screen base and split by underscore
        $base  = $screen->base;
        $parts = explode('_', $base);

        // If no parts or the first part is incorrect, stop
        if (count($parts) === 0 || ($parts[0] !== 'toplevel' && $parts[0] !== 'bookings')) {
            return null;
        }

        // Get the last part
        $pageId = end($parts);

        // Check for prefix
        if (strpos($pageId, 'eddbk-') !== 0) {
            return null;
        }

        return substr($pageId, strlen('eddbk-'));
    }

    /**
     * Retrieves the current WordPress screen.
     *
     * @since [*next-version*]
     *
     * @return \WP_Screen|null The screen object or null if not applicable.
     */
    abstract protected function _wpGetCurrentScreen();

    /**
     * Retrieves whether the current screen is a WP Admin screen
     *
     * @since [*next-version*]
     *
     * @return bool True if the current screen is a WP Admin screen, false if not.
     */
    abstract protected function _wpIsAdmin();
}
