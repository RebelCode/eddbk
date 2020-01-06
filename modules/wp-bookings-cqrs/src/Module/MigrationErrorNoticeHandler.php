<?php

namespace RebelCode\Storage\Resource\WordPress\Module;

use Dhii\Event\EventFactoryInterface;
use Dhii\Invocation\InvocableInterface;
use Exception;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * The migration failure error notice handler.
 *
 * This implementation shows a notice if the migration failure transient is set.
 *
 * @since [*next-version*]
 */
class MigrationErrorNoticeHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager The event manager, used to attach WordPress admin notice events.
     * @param EventFactoryInterface $eventFactory The event factory for creating event instances.
     */
    public function __construct(EventManagerInterface $eventManager, EventFactoryInterface $eventFactory)
    {
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
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $exception = $event->getParam('exception');

        if (!($exception instanceof Exception)) {
            return;
        }

        $message   = $exception->getMessage();
        $errors    = array_filter(explode("\n", $message));
        $errorList = '<li>' . implode('</li><li>', $errors) . '</li>';
        $message   = $this->__('EDD Bookings failed to migrate. Reasons:');

        $this->_attach('admin_notices', function () use ($message, $errorList) {
            printf('<div class="notice notice-error is-dismissible"><p>%s</p><ol>%s</ol></div>', $message, $errorList);
        });
    }
}
