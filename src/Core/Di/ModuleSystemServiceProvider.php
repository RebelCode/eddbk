<?php

namespace RebelCode\EddBookings\Core\Di;

use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\AbstractBaseServiceProvider;
use RebelCode\Modular\Config\ConfigInterface;
use RebelCode\Modular\Config\Validator;
use RebelCode\Modular\Factory\ModuleFactory;
use RebelCode\Modular\Finder\ModuleFileFinder;
use RebelCode\Modular\Loader\LoopMachineModuleLoader;
use RebelCode\Modular\Locator\FileLocator;
use RebelCode\Modular\Module\Module;

/**
 * A service provider for the components of the module system.
 *
 * @since [*next-version*]
 */
class ModuleSystemServiceProvider extends AbstractBaseServiceProvider
{
    /**
     * The service ID for a module.
     *
     * @since [*next-version*]
     */
    const SID_MODULE = 'module';

    /**
     * The service ID for the module configuration validator.
     *
     * @since [*next-version*]
     */
    const SID_MODULE_CONFIG_VALIDATOR = 'module_config_validator';

    /**
     * The service ID for a module find finder.
     *
     * @since [*next-version*]
     */
    const SID_MODULE_FILE_FINDER = 'module_file_finder';

    /**
     * The service ID for a module locator.
     *
     * @since [*next-version*]
     */
    const SID_MODULE_LOCATOR = 'module_locator';

    /**
     * The service ID for a module factory.
     *
     * @since [*next-version*]
     */
    const SID_MODULE_FACTORY = 'module_factory';

    /**
     * The service ID for a module loader.
     *
     * @since [*next-version*]
     */
    const SID_MODULE_LOADER = 'module_loader';

    /**
     * The service ID of the generic factory.
     *
     * @since[*next-version*]
     *
     * @var string
     */
    protected $factoryServiceId;

    /**
     * The service ID of the loop machine.
     *
     * @since[*next-version*]
     *
     * @var string
     */
    protected $loopMachineServiceId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct($factoryServiceId, $loopMachineServiceId)
    {
        $this->_setFactoryServiceId($factoryServiceId)
             ->_setLoopMachineServiceId($loopMachineServiceId);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServices()
    {
        return $this->_prepare(array(
            static::SID_MODULE                  => 'getModule',
            static::SID_MODULE_FILE_FINDER      => 'getModuleFileFinder',
            static::SID_MODULE_CONFIG_VALIDATOR => 'getModuleValidator',
            static::SID_MODULE_LOCATOR          => 'getModuleLocator',
            static::SID_MODULE_FACTORY          => 'getModuleFactory',
            static::SID_MODULE_LOADER           => 'getModuleLoader',
        ));
    }

    /**
     * Retrieves the factory service ID.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _getFactoryServiceId()
    {
        return $this->factoryServiceId;
    }

    /**
     * Sets the factory service ID.
     *
     * @since [*next-version*]
     *
     * @param string $factoryServiceId The factory service ID.
     *
     * @return $this
     */
    protected function _setFactoryServiceId($factoryServiceId)
    {
        $this->factoryServiceId = $factoryServiceId;

        return $this;
    }

    /**
     * Retrieves the loop machine service ID.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _getLoopMachineServiceId()
    {
        return $this->loopMachineServiceId;
    }

    /**
     * Sets the loop machine service ID.
     *
     * @since [*next-version*]
     *
     * @param string $loopMachineServiceId The loop machine service ID.
     *
     * @return $this
     */
    protected function _setLoopMachineServiceId($loopMachineServiceId)
    {
        $this->loopMachineServiceId = $loopMachineServiceId;

        return $this;
    }

    /**
     * Service definition for a module.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return Module
     */
    public function getModule(ContainerInterface $c, $previous = null, array $config = array())
    {
        $key    = $config[ConfigInterface::K_KEY];
        $deps   = $config[ConfigInterface::K_DEPENDENCIES];
        $onLoad = $config[ConfigInterface::K_ON_LOAD];

        return new Module($key, $deps, $config, $onLoad);
    }

    /**
     * Service definition for a module file finder.
     *
     * @since[*next-version*]
     *
     * @param ContainerInterface $c The container instance.
     * @param mixed $previous The previous instance, if any. Default: null
     * @param array $config Any configuration data. Default: array()
     *
     * @return ModuleFileFinder
     */
    public function getModuleFileFinder(ContainerInterface $c, $previous = null, array $config = [])
    {
        return new ModuleFileFinder(EDDBK_VENDOR_DIR, EDDBK_MODULE_FILE_FINDER_MAX_DEPTH);
    }

    /**
     * Service definition for a module configuration validator.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return Validator
     */
    public function getModuleValidator(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new Validator();
    }

    /**
     * Service definition for a module locator.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return FileLocator
     */
    public function getModuleLocator(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new FileLocator(
            $c->get(static::SID_MODULE_FILE_FINDER),
            $c->get(static::SID_MODULE_CONFIG_VALIDATOR)
        );
    }

    /**
     * Service definition for a module factory.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return ModuleFactory
     */
    public function getModuleFactory(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new ModuleFactory(
            $c->get($this->_getFactoryServiceId()),
            static::SID_MODULE
        );
    }

    /**
     * Service definition for a module loader.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return LoopMachineModuleLoader
     */
    public function getModuleLoader(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new LoopMachineModuleLoader($c->get($this->_getLoopMachineServiceId()));
    }
}
