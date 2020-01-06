<?php

namespace RebelCode\Bookings\Module;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use RebelCode\State\TransitionEvent;

/**
 * A factory for creating transition event instances.
 *
 * @since [*next-version*]
 */
class TransitionEventFactory implements EventFactoryInterface
{
    /*
     * Provides functionality for reading from any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides functionality for key-checking any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * Provides key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides functionality for creating invalid-argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating container not-found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The key for the event name in the config.
     *
     * @since [*next-version*]
     */
    const K_CFG_NAME = 'name';

    /**
     * The key for the event params in the config.
     *
     * @since [*next-version*]
     */
    const K_CFG_PARAMS = 'params';

    /**
     * The key for the event target in the config.
     *
     * @since [*next-version*]
     */
    const K_CFG_TARGET = 'target';

    /**
     * The key for the event propagation flag in the config.
     *
     * @since [*next-version*]
     */
    const K_CFG_PROPAGATION = 'propagation';

    /**
     * The key for the event transition in the config.
     *
     * @since [*next-version*]
     */
    const K_CFG_TRANSITION = 'transition';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $name       = $this->_containerGet($config, static::K_CFG_NAME);
        $transition = $this->_containerGet($config, static::K_CFG_TRANSITION);

        $params = $this->_containerHas($config, static::K_CFG_PARAMS)
            ? $this->_containerGet($config, static::K_CFG_PARAMS)
            : null;
        $target = $this->_containerHas($config, static::K_CFG_TARGET)
            ? $this->_containerGet($config, static::K_CFG_TARGET)
            : null;

        return new TransitionEvent($name, $transition, $target, $params);
    }
}
