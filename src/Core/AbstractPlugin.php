<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Di\FactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Modular\Module\AbstractModule;

/**
 * Common functionality for modules that represent a WordPress plugin.
 *
 * @since[*next-version*]
 */
abstract class AbstractPlugin extends AbstractModule
{
    /**
     * The container instance.
     *
     * @since[*next-version*]
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The factory instance.
     *
     * @since[*next-version*]
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the container instance.
     *
     * @param ContainerInterface $container
     *
     * @return $this
     */
    protected function _setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Sets the factory instance.
     *
     * @param FactoryInterface $factory
     *
     * @return $this
     */
    protected function _setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;

        return $this;
    }
}
