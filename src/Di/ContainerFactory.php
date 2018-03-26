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
    use ContainerGetCapableTrait;

    use ContainerHasCapableTrait;

    use NormalizeKeyCapableTrait;

    use NormalizeStringCapableTrait;

    use CreateNotFoundExceptionCapableTrait;

    use CreateContainerExceptionCapableTrait;

    use CreateInvalidArgumentExceptionCapableTrait;

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
        $container = $this->_containerHas($config, static::K_CFG_PARENT_CONTAINER);

        return new Container(new MemoryMemoizer(), $definitions, $container);
    }
}
