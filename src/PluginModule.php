<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Collection\AddCapableOrderedList;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\EventManager\WordPress\WpEventManager;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\CreateReflectionForCallableCapableTrait;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Invocation\NormalizeCallableCapableTrait;
use Dhii\Invocation\NormalizeMethodCallableCapableTrait;
use Dhii\Invocation\ValidateParamsCapableTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Modular\Config\ModuleConfigAwareInterface;
use Dhii\Modular\Module\ModuleInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\CreateValidationFailedExceptionCapableTrait;
use Dhii\Validation\GetArgsListErrorsCapableTrait;
use Dhii\Validation\GetValueTypeErrorCapableTrait;
use Exception;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\CompositeContainer;
use RebelCode\EddBookings\Core\Di\ContainerFactory;
use RebelCode\Modular\Events\EventFactory;
use RebelCode\Modular\Iterator\DependencyModuleIterator;
use RebelCode\Modular\Module\AbstractBaseModularModule;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
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
     * @param string|Stringable         $moduleKey            The key of the module.
     * @param ContainerFactoryInterface $containerFactory     The factory for creating containers.
     * @param ContainerFactoryInterface $compContainerFactory The factory for creating composite containers.
     * @param array|Traversable         $moduleFiles          The module file paths of the modules to be loaded, if any.
     */
    public function __construct(
        $moduleKey,
        ContainerFactoryInterface $containerFactory,
        ContainerFactoryInterface $compContainerFactory,
        $moduleFiles = []
    ) {
        $this->_initModularModule($compContainerFactory, $containerFactory, $moduleKey, [], []);
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
            $_module = call_user_func_array($_callback, [$container]);

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
    protected function _getInitialContainer(ContainerInterface $parent = null)
    {
        return $this->_createContainer(
            [
                'composite_container_factory' => function() {
                    return $this->_getCompositeContainerFactory();
                },
                'container_factory' => function() use ($parent) {
                    return new ContainerFactory($parent);
                },
                'event_manager' => function () {
                    return new WpEventManager(true);
                },
                'event_factory' => function() {
                    return new EventFactory();
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createAddCapableList()
    {
        return new AddCapableOrderedList();
    }
}
