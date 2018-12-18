<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Cache\MemoryMemoizer;
use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Di\ContainerAwareCachingContainer;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;

/**
 * A factory for creating containers for EDD Bookings.
 *
 * @since 0.1
 */
class ContainerFactory implements ContainerFactoryInterface
{
    /*
     * @since 0.1
     */
    use ContainerAwareTrait;

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
     * The key in the config for the parent container.
     *
     * @since 0.1
     */
    const K_CFG_PARENT_CONTAINER = 'parent';

    /**
     * Constructor.
     *
     * @since 0.1
     *4
     *
     * @param ContainerInterface|null $parentContainer The default parent container to assign to created containers.
     */
    public function __construct(ContainerInterface $parentContainer = null)
    {
        $this->_setContainer($parentContainer);
    }

    /**
     * {@inheritdoc}
     *
     * @since 0.1
     */
    public function make($config = null)
    {
        $definitions = $this->_containerGet($config, ContainerFactoryInterface::K_CFG_DEFINITIONS);
        $container   = $this->_containerHas($config, static::K_CFG_PARENT_CONTAINER)
            ? $this->_containerGet($config, static::K_CFG_PARENT_CONTAINER)
            : $this->_getContainer();

        return new ContainerAwareCachingContainer($definitions, new MemoryMemoizer(), $container);
    }
}
