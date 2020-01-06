<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;

/**
 * Handler for UI state config. In the config we pass data needed by UI application to
 * work correctly. Such as site timezone and formats configuration.
 *
 * @since [*next-version*]
 */
class FrontApplicationLabelsHandler implements InvocableInterface
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
     * List of localized default labels.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $localizedDefaultLabels;

    /**
     * GeneralUiStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $settingsContainer      Settings container.
     * @param array              $localizedDefaultLabels Default localized front application labels.
     */
    public function __construct(
        $settingsContainer,
        $localizedDefaultLabels
    ) {
        $this->settingsContainer      = $settingsContainer;
        $this->localizedDefaultLabels = $localizedDefaultLabels;
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
            'labels' => array_replace_recursive(
                $this->localizedDefaultLabels,
                $this->_normalizeArray($this->settingsContainer->get('booking_wizard_labels'))
            ),
        ]);
    }
}
