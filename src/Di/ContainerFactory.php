<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Cache\MemoryMemoizer;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;

/**
 * A factory for creating containers for EDD Bookings.
 *
 * @since [*next-version*]
 */
class ContainerFactory implements ContainerFactoryInterface
{
    /*
     * @since [*next-version*]
     */
    use ContainerAwareTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The key in the config for the parent container.
     *
     * @since [*next-version*]
     */
    const K_CFG_PARENT_CONTAINER = 'parent';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $definitions = $this->_containerGet($config, ContainerFactoryInterface::K_CFG_DEFINITIONS);
        $container = $this->_containerHas($config, static::K_CFG_PARENT_CONTAINER)
            ? $this->_containerGet($config, static::K_CFG_PARENT_CONTAINER)
            : null;

        return new Container(new MemoryMemoizer(), $definitions, $container);
    }
}
