<?php


namespace RebelCode\EddBookings\Core;

use Dhii\Factory\FactoryInterface;
use Dhii\Modular\Factory\ModuleFactoryInterface;
use Dhii\Modular\Loader\ModuleLoaderInterface;
use Dhii\Modular\Locator\ModuleLocatorInterface;
use Dhii\Modular\Module\ModuleInterface;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\ModuleSystemServiceProvider;

/**
 * Plugin hub class for EDD Bookings.
 *
 * @since[*next-version*]
 */
class Plugin implements PluginInterface
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
     * The loaded modules.
     *
     * @since[*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $loadModules;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $container The container instance.
     * @param FactoryInterface   $factory   The factory instance.
     */
    public function __construct(ContainerInterface $container, FactoryInterface $factory)
    {
        $this->_setContainer($container)
            ->_setFactory($factory);
    }

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

    /**
     * Retrieves the loaded modules.
     *
     * @since[*next-version*]
     *
     * @return ModuleInterface[]|\Traversable
     */
    protected function _getLoadedModules()
    {
        return $this->loadModules;
    }

    /**
     * Sets the loaded modules.
     *
     * @since[*next-version*]
     *
     * @param ModuleInterface[]|\Traversable $modules
     *
     * @return $this
     */
    protected function _setLoadedModules($modules)
    {
        $this->loadModules = $modules;

        return $this;
    }

    /**
     * Retrieves the module locator.
     *
     * @since[*next-version*]
     *
     * @return ModuleLocatorInterface
     */
    protected function _getModuleLocator()
    {
        return $this->getContainer()->get(ModuleSystemServiceProvider::SID_MODULE_LOCATOR);
    }

    /**
     * Description
     *
     * @since[*next-version*]
     *
     *
     * @return ModuleFactoryInterface
     */
    protected function _getModuleFactory()
    {
        return $this->getContainer()->get(ModuleSystemServiceProvider::SID_MODULE_FACTORY);
    }

    /**
     * Retrieves the module loader.
     *
     * @since[*next-version*]
     *
     * @return ModuleLoaderInterface
     */
    protected function _getModuleLoader()
    {
        return $this->getContainer()->get(ModuleSystemServiceProvider::SID_MODULE_LOADER);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run()
    {
        do_action('before_eddbk_run');

        $configs = $this->_getModuleLocator()->locate();
        $modules = array_map([$this->_getModuleFactory(), 'makeModule'], $configs);

        $this->_getModuleLoader()->load($modules);
        $this->_setLoadedModules($modules);

        do_action('after_eddbk_run');
    }
}
