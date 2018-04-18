<?php

namespace RebelCode\EddBookings\Core\Util;

use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;

/**
 * A factory for creating config containers for EDD Bookings.
 *
 * @since [*next-version*]
 */
class ConfigFactory implements ContainerFactoryInterface
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
     * The key in the config for the config data.
     *
     * @since [*next-version*]
     */
    const K_CFG_DATA = 'data';

    /**
     * The key in the config for the parent container.
     *
     * @since [*next-version*]
     */
    const K_CFG_PARENT_CONTAINER = 'parent';

    /**
     * Constructor.
     *
     * @since [*next-version*]
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
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        $data   = $this->_containerGet($config, static::K_CFG_DATA);
        $parent = $this->_containerHas($config, static::K_CFG_PARENT_CONTAINER)
            ? $this->_containerGet($config, static::K_CFG_PARENT_CONTAINER)
            : $this->_getContainer();

        return new Config($data, $parent);
    }
}
