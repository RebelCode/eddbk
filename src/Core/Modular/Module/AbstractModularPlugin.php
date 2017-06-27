<?php

namespace RebelCode\EddBookings\Core\Modular\Module;

use Dhii\Modular\Factory\ModuleFactoryInterface;
use Dhii\Modular\Loader\ModuleLoaderInterface;
use Dhii\Modular\Locator\ModuleLocatorInterface;
use Dhii\Modular\Module\ModuleInterface;

/**
 * Common functionality for plugins that can load other modules.
 *
 * @since[*next-version*]
 */
abstract class AbstractModularPlugin extends AbstractPlugin
{
    /**
     * The loaded modules.
     *
     * @since[*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $loadedModules;

    /**
     * Parameter-less constructor.
     *
     * Call this in the real constructor.
     *
     * @since[*next-version*]
     */
    protected function _construct()
    {
        parent::_construct();
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
        return $this->loadedModules;
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
        $this->loadedModules = $modules;

        return $this;
    }

    /**
     * Loads the modules.
     *
     * @since[*next-version*]
     *
     * @return $this
     */
    protected function _loadModules()
    {
        $configs = $this->_getModuleLocator()->locate();
        $modules = array_map(array($this->_getModuleFactory(), 'makeModule'), $configs);

        $this->_getModuleLoader()->load($modules);
        $this->_setLoadedModules($modules);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _load()
    {
        parent::_load();

        $this->_loadModules();

        return $this;
    }

    /**
     * Retrieves the module locator.
     *
     * @since[*next-version*]
     *
     * @return ModuleLocatorInterface
     */
    abstract protected function _getModuleLocator();

    /**
     * Retrieves the module factory.
     *
     * @since[*next-version*]
     *
     * @return ModuleFactoryInterface
     */
    abstract protected function _getModuleFactory();

    /**
     * Retrieves the module loader.
     *
     * @since[*next-version*]
     *
     * @return ModuleLoaderInterface
     */
    abstract protected function _getModuleLoader();
}
