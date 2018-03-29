<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Modular\Config\ModuleConfigAwareInterface;
use Dhii\Modular\Module\ModuleInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\CompositeContainer;
use RebelCode\Modular\Iterator\DependencyModuleIterator;
use RebelCode\Modular\Module\AbstractBaseModularModule;
use ReflectionClass;
use ReflectionProperty;
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
class PluginModule extends AbstractBaseModularModule implements ModuleConfigAwareInterface
{
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
     * @param ContainerFactoryInterface $containerFactory     The factory for creating containers.
     * @param ContainerFactoryInterface $compContainerFactory The factory for creating composite containers.
     * @param array|Traversable         $moduleFiles          The module file paths of the modules to be loaded, if any.
     */
    public function __construct(
        ContainerFactoryInterface $containerFactory,
        ContainerFactoryInterface $compContainerFactory,
        $moduleFiles = []
    ) {
        $this->_initModularModule($compContainerFactory, $containerFactory, EDDBK_SLUG, [], []);
        $this->moduleFiles = $moduleFiles;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setup()
    {
        $container =  $this->_setup();

        try {
            if ($container instanceof CompositeContainer) {
                // Quick fix for container composition
                foreach ($container->getContainers() as $_child) {
                    $_pParent = new ReflectionProperty($_child, 'parent');
                    $_pParent->setAccessible(true);
                    $_pParent->setValue($_child, $container);
                }
            }
        } catch (Exception $exception) {
        }

        $this->_setConfig(['modules' => $this->modules]);

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
    protected function _getModules(ContainerInterface $container = null)
    {
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
    protected function _getModuleInitContainer()
    {
        return $this->_createContainer(
            [
                'container_factory' => function() {
                    return $this->_getContainerFactory();
                },
            ]
        );
    }
}
