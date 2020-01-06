<?php

namespace RebelCode\EddBookings\RestApi\Auth;

use Dhii\Event\EventFactoryInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\AbstractValidatorBase;
use Dhii\Validation\ValidatorInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * An authorization validator uses filters a boolean flag to determine authorization.
 *
 * @since [*next-version*]
 */
class FilterAuthValidator extends AbstractValidatorBase implements ValidatorInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * The name of the event to trigger.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $eventName;

    /**
     * The key to use in event params for the boolean flag.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $paramKey;

    /**
     * The default boolean value to use for the boolean flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $paramDefault;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager The event manager instance to use for triggering the filter event.
     * @param EventFactoryInterface $eventFactory The event factory to use for creating event instances.
     * @param string|Stringable     $eventName    The name of the event to trigger.
     * @param string|Stringable     $paramKey     The key to use in event params for the boolean flag.
     * @param bool                  $paramDefault The default boolean value to use for the boolean flag.
     */
    public function __construct(
        EventManagerInterface $eventManager,
        EventFactoryInterface $eventFactory,
        $eventName,
        $paramKey,
        $paramDefault = false
    ) {
        $this->paramKey     = $paramKey;
        $this->paramDefault = $paramDefault;
        $this->eventName    = $eventName;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getValidationErrors($request)
    {
        $event  = $this->_trigger($this->eventName, [
            'request'       => $request,
            $this->paramKey => $this->paramDefault,
        ]);
        $isAuth = $event->getParam($this->paramKey);
        $errors = [];

        if (!$isAuth) {
            $errors[] = $this->__('You are not authorized to access this route');
        }

        return $errors;
    }
}
