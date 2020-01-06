<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Cache\SimpleCacheInterface;
use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use Psr\EventManager\EventInterface;
use Dhii\Event\EventFactoryInterface;
use stdClass;
use Traversable;

/**
 * Handler for adding statuses information to state on bookings page.
 * It will add list of available statuses, list of visible booking statuses to filter bookings
 * and endpoint for saving list of visible booking statuses.
 *
 * @since [*next-version*]
 */
class BookingsStateStatusesHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use GetVisibleStatusesCapable;

    /**
     * List of statuses keys available in application.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $statuses;

    /**
     * Option key name to save screen statuses config.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screenOptionsKey;

    /**
     * Available fields for screen options.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $screenOptionsFields;

    /**
     * Endpoint for saving screen options.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screenOptionsEndpoint;

    /**
     * The screen options cache.
     *
     * @since [*next-version*]
     *
     * @var SimpleCacheInterface
     */
    protected $screenOptionsCache;

    /**
     * StatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass  $statuses              List of statuses key in application.
     * @param string                      $screenOptionsKey      Option key name to save screen statuses config.
     * @param array|stdClass|MapInterface $screenOptionsFields   Available fields for screen options.
     * @param string                      $screenOptionsEndpoint Endpoint for saving screen options.
     * @param SimpleCacheInterface        $screenOptionsCache    The screen options cache.
     * @param EventManagerInterface       $eventManager          The event manager.
     * @param EventFactoryInterface       $eventFactory          The event factory.
     */
    public function __construct(
        $statuses,
        $screenOptionsKey,
        $screenOptionsFields,
        $screenOptionsEndpoint,
        $screenOptionsCache,
        $eventManager,
        $eventFactory
    ) {
        $this->statuses = $statuses;

        $this->screenOptionsKey      = $screenOptionsKey;
        $this->screenOptionsFields   = $screenOptionsFields;
        $this->screenOptionsEndpoint = $screenOptionsEndpoint;
        $this->screenOptionsCache    = $screenOptionsCache;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
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

        if (!$user = wp_get_current_user()) {
            return;
        }

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getStatusesParams($user->ID)
        ));
    }

    /**
     * Get status related parameters to attach on event.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     *
     * @return array
     */
    protected function _getStatusesParams($userId)
    {
        return [
            /*
             * List of booking statuses in screen options section that is checked for filtering bookings.
             */
            'screenStatuses' => $this->_getScreenStatuses($userId, $this->statuses),

            /*
             * Endpoint for saving screen options.
             */
            'screenOptionsEndpoint' => $this->screenOptionsEndpoint,

            /*
             * Timezone name for bookings page.
             */
            'bookingsTimezone' => $this->_getBookingsTimezone($userId),
        ];
    }

    /**
     * Return list of all statuses that will be shown for user by default.
     *
     * @since [*next-version*]
     *
     * @param int                        $userId          User identifier.
     * @param array|Traversable|stdClass $defaultStatuses List of booking statuses selected by default
     *
     * @return string[] List of statuses that user selected to show by default
     */
    protected function _getScreenStatuses($userId, $defaultStatuses = [])
    {
        $statusesKey = $this->_containerGet($this->screenOptionsFields, 'statuses');
        $statuses    = $this->_getScreenOption($userId, $statusesKey, $defaultStatuses);

        return $this->_getVisibleStatuses($statuses);
    }

    /**
     * Get timezone for bookings page for current user.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     *
     * @return string|null Timezone identifier for bookings page for current user.
     */
    protected function _getBookingsTimezone($userId)
    {
        $bookingsTimezoneKey = $this->_containerGet($this->screenOptionsFields, 'bookingsTimezone');

        return $this->_getScreenOption($userId, $bookingsTimezoneKey);
    }

    /**
     * Get value from screen options by key.
     *
     * @since [*next-version*]
     *
     * @param int    $userId       User identifier.
     * @param string $key          Key to get from screen options.
     * @param mixed  $defaultValue Value to return if key not found in screen options.
     *
     * @return mixed
     */
    protected function _getScreenOption($userId, $key, $defaultValue = null)
    {
        $screenOptions = $this->_getScreenOptions($userId);

        $value = $defaultValue;
        if ($screenOptions && $this->_containerHas($screenOptions, $key)) {
            $value = $this->_containerGet($screenOptions, $key);
        }

        return $value;
    }

    /**
     * Get screen options for given user.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     *
     * @return array|stdClass|null
     */
    protected function _getScreenOptions($userId)
    {
        $userIdKey = $this->_normalizeKey($userId);

        return $this->screenOptionsCache->get($userIdKey, function () use ($userId) {
            $screenOptions = $this->_getUserOption($userId, $this->screenOptionsKey);
            if (!$screenOptions) {
                return;
            }

            return json_decode($screenOptions);
        });
    }

    /**
     * Retrieve user option.
     *
     * @since [*next-version*]
     *
     * @param int|float|string|Stringable $userId User ID.
     * @param string|Stringable           $key    User option key.
     *
     * @return mixed User option value on success, false on failure.
     */
    protected function _getUserOption($userId, $key)
    {
        $userId = $this->_normalizeInt($userId);
        $key    = $this->_normalizeString($key);

        return get_user_option($key, $userId);
    }
}
