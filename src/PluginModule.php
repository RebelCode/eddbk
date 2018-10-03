<?php

namespace RebelCode\EddBookings\Core;

use ArrayAccess;
use Dhii\Collection\AddCapableOrderedList;
use Dhii\Config\ConfigFactoryInterface;
use Dhii\Config\DereferencingConfigMapFactory;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalExceptionInterface;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Modular\Module\ModuleInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\Core\Di\ContainerFactory;
use RebelCode\Modular\Iterator\DependencyModuleIterator;
use RebelCode\Modular\Module\AbstractBaseModularModule;
use stdClass;
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
class PluginModule extends AbstractBaseModularModule
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
     * The plugin info.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|ArrayAccess|ContainerInterface
     */
    protected $pluginInfo;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $pluginInfo           The plugin info.
     * @param ConfigFactoryInterface                        $configFactory        The config factory.
     * @param ContainerFactoryInterface                     $containerFactory     The factory for creating containers.
     * @param ContainerFactoryInterface                     $compContainerFactory The composite container factory.
     * @param EventManagerInterface                         $eventManager         The event manager instance.
     * @param EventFactoryInterface                         $eventFactory         The factory for creating events.
     * @param array|Traversable                             $moduleFiles          The module file paths of the modules
     *                                                                            to be loaded, if any.
     */
    public function __construct(
        $pluginInfo,
        ConfigFactoryInterface $configFactory,
        ContainerFactoryInterface $containerFactory,
        ContainerFactoryInterface $compContainerFactory,
        EventManagerInterface $eventManager,
        EventFactoryInterface $eventFactory,
        $moduleFiles = []
    ) {
        $this->pluginInfo = $pluginInfo;
        $this->moduleFiles = $moduleFiles;

        $moduleKey = $this->_containerGet($this->pluginInfo, 'slug');

        $this->_initModule($moduleKey, [], $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalExceptionInterface
     */
    public function setup()
    {
        $setupContainer = $this->_setup();

        $servicesFilePath = $this->_containerGet($this->pluginInfo, 'services_file_path');
        $container        = $this->_createContainer($this->_loadPhpConfigFile($servicesFilePath));

        $configFilePath = $this->_containerGet($this->pluginInfo, 'config_file_path');
        $infoConfig     = $this->_createConfig(['eddbk' => $this->pluginInfo]);
        $fileConfig     = $this->_createConfig($this->_loadPhpConfigFile($configFilePath));

        return $this->_createCompositeContainer([
            $fileConfig,
            $infoConfig,
            $setupContainer,
            $container,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->_run($c);

        $this->_attach('plugin_row_meta', $c->get('eddbk_plugin_knowledge_base_link_handler'));
    }

    /**
     * {@inheritdoc}
     *
     * This implementation loads modules by requiring their main file, expecting a callable to be returned.
     * The callable is given a DI container as argument. This container provides two container factories (one for
     * regular DI containers, the other for composite containers), a config factory, an event manager and an event
     * factory. The callable may perform any action that is required. If it returns a {@see ModuleInterface}
     * instance, that instance is registered as a module of the plugin.
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

            $_callback = require_once $_file;
            $_module   = $this->_invokeModuleCallback($_callback, $container);

            if ($_module instanceof ModuleInterface) {
                $modules[] = $_module;
            }
        }

        return new DependencyModuleIterator($modules);
    }

    /**
     * Invokes a module callback.
     *
     * @since [*next-version*]
     *
     * @param callable                $callback  The callback.
     * @param ContainerInterface|null $container The container to give to the callback.
     *
     * @return ModuleInterface|mixed The return value of the callback.
     */
    protected function _invokeModuleCallback($callback, ContainerInterface $container = null)
    {
        return call_user_func_array($callback, [$container]);
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
                'composite_container_factory' => function () {
                    return $this->_getCompositeContainerFactory();
                },
                'container_factory' => function () use ($parent) {
                    return new ContainerFactory($parent);
                },
                'config_factory' => function () use ($parent) {
                    return new DereferencingConfigMapFactory($parent);
                },
                'event_manager' => function () {
                    return $this->_getEventManager();
                },
                'event_factory' => function () {
                    return $this->_getEventFactory();
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
