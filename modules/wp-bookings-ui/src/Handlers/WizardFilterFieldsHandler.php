<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;

/**
 * Handler retrieving fields.
 *
 * @since [*next-version*]
 */
class WizardFilterFieldsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /**
     * Settings container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $settingsContainer;

    /**
     * WizardFilterFieldsHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $settingsContainer Settings container.
     */
    public function __construct($settingsContainer)
    {
        $this->settingsContainer = $settingsContainer;
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

        $event->setParams([
            'fields' => $this->settingsContainer->get('booking_wizard_fields'),
        ]);
    }
}
