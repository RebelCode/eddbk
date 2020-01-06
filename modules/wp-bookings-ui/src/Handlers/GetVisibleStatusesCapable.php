<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\InternalExceptionInterface;
use Psr\EventManager\EventInterface;
use RuntimeException;
use stdClass;
use Traversable;

trait GetVisibleStatusesCapable
{
    /**
     * Get list of statuses keys that should be visible in UI.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $statuses List of statuses key to filter.
     *
     * @return string[] List of statuses keys without hidden ones.
     */
    protected function _getVisibleStatuses($statuses)
    {
        return $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $statuses,
        ])->getParam('statuses');
    }

    /**
     * Triggers an event dispatch.
     *
     * @since [*next-version*]
     *
     * @param string|EventInterface $event The event key or instance.
     * @param array|object          $data  The data of the event, if any.
     *
     * @throws RuntimeException           If an error occurred and the event could not be dispatched.
     * @throws InternalExceptionInterface If an error occurred while dispatching the event.
     *
     * @return EventInterface The event instance.
     */
    abstract protected function _trigger($event, $data = []);
}
