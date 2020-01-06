<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventManagerInterface;

/**
 * Factory for creating wizard blocks.
 *
 * @since [*next-version*]
 */
class WizardBlockFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /**
     * Config key of context.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    const K_CFG_CONTEXT = 'context';

    /**
     * Wizard template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $wizardTemplate;

    /**
     * The event manager.
     *
     * @since [*next-version*]
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * The event factory.
     *
     * @since [*next-version*]
     *
     * @var EventFactoryInterface
     */
    protected $eventFactory;

    /**
     * WizardBlockFactory constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface     $wizardTemplate Wizard template.
     * @param EventManagerInterface $eventManager   The event manager.
     * @param EventFactoryInterface $eventFactory   The event factory.
     */
    public function __construct(
        $wizardTemplate,
        $eventManager,
        $eventFactory
    ) {
        $this->wizardTemplate = $wizardTemplate;
        $this->eventManager   = $eventManager;
        $this->eventFactory   = $eventFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return WizardBlock Configured WizardBlock instance.
     */
    public function make($config = null)
    {
        $context = $this->_containerGet($config, static::K_CFG_CONTEXT);

        return new WizardBlock(
            $context,
            $this->wizardTemplate,
            $this->eventManager,
            $this->eventFactory
        );
    }
}
