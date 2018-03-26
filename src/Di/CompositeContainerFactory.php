<?php

namespace RebelCode\EddBookings\Core\Di;

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
 * A factory for creating composite containers for EDD Bookings.
 *
 * @since [*next-version*]
 */
class CompositeContainerFactory implements ContainerFactoryInterface
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
     * The key in the config for the child containers.
     *
     * @since [*next-version*]
     */
    const K_CFG_CHILD_CONTAINERS = 'containers';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $containers = $this->_containerHas($config, static::K_CFG_CHILD_CONTAINERS);

        return new CompositeContainer($containers);
    }
}
