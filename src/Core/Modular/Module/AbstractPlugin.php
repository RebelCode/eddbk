<?php

namespace RebelCode\EddBookings\Core\Modular\Module;

use Dhii\Di\FactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Modular\Config\PluginConfigInterface;
use RebelCode\Modular\Module\AbstractModule;

/**
 * Common functionality for modules that represent a WordPress plugin.
 *
 * @since[*next-version*]
 */
abstract class AbstractPlugin extends AbstractModule
{
    /**
     * The plugin name.
     *
     * @since[*next-version*]
     *
     * @var string
     */
    protected $name;

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
     * Parameter-less constructor.
     *
     * Call this in the real constructor.
     *
     * @since[*next-version*]
     */
    protected function _construct()
    {
    }

    /**
     * Retrieves the plugin name.
     *
     * @since[*next-version*]
     *
     * @return string
     */
    protected function _getName()
    {
        return $this->name;
    }

    /**
     * Sets the plugin name.
     *
     * @since[*next-version*]
     *
     * @param string $name The plugin name.
     *
     * @return $this
     */
    protected function _setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getContainer()
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
    protected function _getFactory()
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

    /**
     * Creates dependency configuration data.
     *
     * @since[*next-version*]
     *
     * @param string $slug    The dependency plugin slug.
     * @param string $name    The dependency plugin name.
     * @param string $version The dependency plugin version.
     *
     * @return array
     */
    protected function _createDependency($slug, $name, $version)
    {
        return array(
            PluginConfigInterface::K_DEPENDENCY_SLUG    => $slug,
            PluginConfigInterface::K_DEPENDENCY_NAME    => $name,
            PluginConfigInterface::K_DEPENDENCY_VERSION => $version,
        );
    }
}
