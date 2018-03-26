<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Cache\MemoryMemoizer;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Modular\Config\ModuleConfigAwareInterface;
use Dhii\Modular\Module\ModuleInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\CompositeContainer;
use RebelCode\EddBookings\Core\Di\Container;
use RebelCode\Modular\Iterator\DependencyModuleIterator;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Modular\Module\ModularModuleTrait;
use Traversable;

/**
 * The EDD Bookings core plugin module.
 *
 * This module is responsible for setting up the plugin by loading other modules.
 *
 * On setup, a "master" composite container is constructed with a child container that contains all of the module
 * instances. Each module is set up and their containers are retrieved, if any, and attached to the master container,
 * which is then returned.
 *
 * On run, the modules are invoked one by one, each receiving the given container. This can be considered the
 * "invocation" of the plugin.
 *
 * @since [*next-version*]
 */
class PluginModule extends AbstractBaseModule implements ModuleConfigAwareInterface
{
    /*
     * Provides functionality for modules that load other modules.
     *
     * @since [*next-version*]
     */
    use ModularModuleTrait;

    /*
     * Provides functionality for invoking callable variables.
     *
     * @since [*next-version*]
     */
    use InvokeCallableCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * Provides functionality for creating invocation exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvocationExceptionCapableTrait;

    /**
     * The modules.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface[]|Traversable
     */
    protected $modules;

    /**
     * The module file paths of the modules to be loaded.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable
     */
    protected $moduleFiles;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerFactoryInterface $containerFactory
     * @param array|Traversable         $moduleFiles The module file paths of the modules to be loaded, if any.
     */
    public function __construct(ContainerFactoryInterface $containerFactory, $moduleFiles = [])
    {
        $this->_initModule($containerFactory, EDDBK_SLUG, [], []);
        $this->moduleFiles = $moduleFiles;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setup()
    {
        $container = $this->_setup();

        $this->_setConfig(
            [
                'modules' => $this->modules,
            ]
        );

        return $container;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->_run($c);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getModuleConfig()
    {
        return $this->_getConfig();
    }

    /**
     * {@inheritdoc}
     *
     * This implementation loads modules by requiring their main file, expecting a callable to be returned.
     * The callable is expected to accept a container and return a module instance.
     *
     * @since [*next-version*]
     */
    protected function _getModules()
    {
        $container = $this->_createContainer(
            [
                'container-factory' => function() {
                    return $this->_getContainerFactory();
                }
            ]
        );

        $modules = [];
        foreach ($this->moduleFiles as $_idx => $_file) {
            if (!file_exists($_file) || !is_readable($_file)) {
                throw $this->_createRuntimeException(
                    $this->__('Module file "%1$s" does not exist or is not readable', [$_file]),
                    null,
                    null
                );
            }

            $_callback = require_once($_file);
            $_module = $this->_invokeCallable($_callback, [$container]);

            if ($_module instanceof ModuleInterface) {
                $modules[] = $_module;
            }
        }

        return new DependencyModuleIterator($modules);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createContainer($definitions = [])
    {
        return new Container(new MemoryMemoizer(), $definitions);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createCompositeContainer($containers)
    {
        return new CompositeContainer($containers);
    }
}
