<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for filtering statuses keys before sending them to the UI.
 * It helps to be sure that only allowed statuses are visible in the UI.
 *
 * @since [*next-version*]
 */
class VisibleStatusesHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * List of statuses keys that are not visible in the UI.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $hiddenStatuses;

    /**
     * VisibleStatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $hiddenStatuses List of bookings statuses that should be hidden in UI
     */
    public function __construct($hiddenStatuses)
    {
        $this->hiddenStatuses = $hiddenStatuses;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $statuses = $event->getParam('statuses');

        if (!$statuses) {
            throw $this->_createInvalidArgumentException(
                $this->__('Statuses parameter is required for filtering'), null, null, $event
            );
        }

        $event->setParams([
            'statuses' => $this->_getVisibleStatuses($statuses, $this->hiddenStatuses),
        ]);
    }

    /**
     * Get array of visible statuses for UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $statuses       List of all statuses.
     * @param array|Traversable|stdClass $hiddenStatuses List of statuses that shouldn't be shown.
     *
     * @return array Resulting array of statuses that should be visible in the UI.
     */
    protected function _getVisibleStatuses($statuses, $hiddenStatuses)
    {
        $statuses       = $this->_normalizeArray($statuses);
        $hiddenStatuses = $this->_normalizeArray($hiddenStatuses);

        return array_values(array_filter($statuses, function ($status) use ($hiddenStatuses) {
            return !in_array($status, $hiddenStatuses);
        }));
    }
}
