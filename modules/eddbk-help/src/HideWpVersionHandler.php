<?php

namespace RebelCode\EddBookings\Help;

use Dhii\Invocation\InvocableInterface;

/**
 * The handler that hides the WP version on EDD Bookings pages, to accommodate the Beacon button.
 *
 * @since [*next-version*]
 */
class HideWpVersionHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use GetEddBkPageIdCapableTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        if ($this->_getEddBkPageId()) {
            // Hide WP Version
            remove_filter('update_footer', 'core_update_footer');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _wpGetCurrentScreen()
    {
        return \get_current_screen();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _wpIsAdmin()
    {
        return \is_admin();
    }
}
