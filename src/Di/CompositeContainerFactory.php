<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Data\Container\CompositeContainer;
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
 * @since 0.1
 */
class CompositeContainerFactory implements ContainerFactoryInterface
{
    /*
     * @since 0.1
     */
    use ContainerGetCapableTrait;

    /*
     * @since 0.1
     */
    use ContainerHasCapableTrait;

    /*
     * @since 0.1
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since 0.1
     */
    use NormalizeStringCapableTrait;

    /*
     * @since 0.1
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * @since 0.1
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since 0.1
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since 0.1
     */
    use StringTranslatingTrait;

    /**
     * The key in the config for the child containers.
     *
     * @since 0.1
     */
    const K_CFG_CHILD_CONTAINERS = 'containers';

    /**
     * {@inheritdoc}
     *
     * @since 0.1
     */
    public function make($config = null)
    {
        $containers = $this->_containerGet($config, static::K_CFG_CHILD_CONTAINERS);

        return new CompositeContainer($containers);
    }
}
