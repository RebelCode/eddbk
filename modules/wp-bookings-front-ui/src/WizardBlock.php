<?php

namespace RebelCode\Bookings\WordPress\Module;

use ArrayAccess;
use Dhii\Event\EventFactoryInterface;
use Dhii\Output\BlockInterface;
use Dhii\Output\TemplateInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use stdClass;

/**
 * Class for showing wizard and enqueuing all assets that are related to it.
 *
 * @since [*next-version*]
 */
class WizardBlock implements BlockInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /**
     * Wizard template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $wizardTemplate;

    /**
     * Context of wizard template.
     *
     * @since [*next-version*]
     *
     * @var array|ArrayAccess|stdClass|ContainerInterface|null
     */
    protected $context;

    /**
     * WizardBlock constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface|null $context        Context of wizard template.
     * @param TemplateInterface                                  $wizardTemplate Wizard template.
     * @param EventManagerInterface                              $eventManager   The event manager.
     * @param EventFactoryInterface                              $eventFactory   The event factory.
     */
    public function __construct(
        $context,
        $wizardTemplate,
        $eventManager,
        $eventFactory
    ) {
        $this->wizardTemplate = $wizardTemplate;
        $this->context        = $context;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render()
    {
        $eventData = [
            'block'    => $this,
            'template' => $this->wizardTemplate,
            'context'  => $this->context,
        ];

        $this->_trigger('before_block_render', $eventData);
        $this->_trigger('before_block_render_wizard', $eventData);

        return $this->wizardTemplate->render($this->context);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __toString()
    {
        return $this->render();
    }
}
