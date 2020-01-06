<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Cache\SimpleCacheInterface;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Handle saving screen options for user.
 *
 * @since [*next-version*]
 */
class SaveScreenOptionsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /**
     * Option key name to save screen statuses config.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screenOptionsKey;

    /**
     * List of screen options field for user.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $screenOptionsFields;

    /**
     * The screen options cache.
     *
     * @since [*next-version*]
     *
     * @var SimpleCacheInterface
     */
    protected $screenOptionsCache;

    /**
     * SaveScreenStatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param string                     $screenOptionsKey    Option key name to save screen statuses config.
     * @param array|stdClass|Traversable $screenOptionsFields List of fields in screen options.
     * @param SimpleCacheInterface       $screenOptionsCache  The screen options cache.
     */
    public function __construct($screenOptionsKey, $screenOptionsFields, $screenOptionsCache)
    {
        $this->screenOptionsKey    = $screenOptionsKey;
        $this->screenOptionsFields = $screenOptionsFields;
        $this->screenOptionsCache  = $screenOptionsCache;
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

        $this->_handle($user->ID);
    }

    /**
     * Save screen options for user.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     */
    protected function _handle($userId)
    {
        $data    = $this->_getRequestData();
        $options = [];
        foreach ($this->screenOptionsFields as $key) {
            if (array_key_exists($key, $data)) {
                $options[$key] = $data[$key];
            }
        }
        $this->_setScreenOptions($userId, $options);
    }

    /**
     * Save screen options in per-user options.
     *
     * @since [*next-version*]
     *
     * @param int            $userId  User identifier.
     * @param array|stdClass $options Screen options to save.
     */
    protected function _setScreenOptions($userId, $options)
    {
        $userIdKey = $this->_normalizeKey($userId);
        $key       = $this->screenOptionsKey;

        $this->_updateUserOption($userId, $key, json_encode($options));

        if ($this->screenOptionsCache->has($userIdKey)) {
            $this->screenOptionsCache->delete($userIdKey);
        }
    }

    /**
     * Get data from request.
     *
     * @since [*next-version*]
     *
     * @return array|mixed|object Request data.
     */
    protected function _getRequestData()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * Update user option.
     *
     * @since [*next-version*]
     *
     * @param int|float|string|Stringable $userId User ID.
     * @param string|Stringable           $key    User option key.
     * @param mixed                       $value  User option value.
     */
    protected function _updateUserOption($userId, $key, $value)
    {
        $userId = $this->_normalizeInt($userId);
        $key    = $this->_normalizeString($key);

        update_user_option($userId, $key, $value);
    }
}
