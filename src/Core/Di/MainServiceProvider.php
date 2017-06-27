<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Machine\LoopMachine;
use Dhii\Modular\Loader\ModuleLoaderInterface;
use Interop\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Modular\Loader\PluginLoader;
use RebelCode\EddBookings\Core\Modular\Module\PluginInterface;
use RebelCode\EddBookings\Core\Plugin;

/**
 * The main service provider.
 *
 * @since[*next-version*]
 */
class MainServiceProvider extends AbstractBaseServiceProvider
{
    /**
     * The service ID of the plugin.
     *
     * @since [*next-version*]
     */
    const SID_PLUGIN = 'plugin';

    /**
     * The service ID of the plugin loader.
     *
     * @since [*next-version*]
     */
    const SID_PLUGIN_LOADER = 'plugin_loader';

    /**
     * The service ID of the factory.
     *
     * @since [*next-version*]
     */
    const SID_FACTORY = 'factory';

    /**
     * The service ID of the loop machine.
     *
     * @since [*next-version*]
     */
    const SID_LOOP_MACHINE = 'loop_machine';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _defaultServices()
    {
        return $this->_prepare(array(
            static::SID_PLUGIN        => 'getPlugin',
            static::SID_PLUGIN_LOADER => 'getPluginLoader',
            static::SID_FACTORY       => 'getFactory',
            static::SID_LOOP_MACHINE  => 'getLoopMachine'
        ));
    }

    /**
     * Service definition for the plugin hub instance.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return PluginInterface
     */
    public function getPlugin(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new Plugin($c, $c->get(static::SID_FACTORY));
    }

    /**
     * Service definition for the plugin loader.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return ModuleLoaderInterface
     */
    public function getPluginLoader(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new PluginLoader();
    }

    /**
     * Service definition for a generic factory.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return ContainerInterface
     */
    public function getFactory(ContainerInterface $c, $previous = null, array $config = array())
    {
        return $c;
    }

    /**
     * Service definition for a loop machine.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return LoopMachine
     */
    public function getLoopMachine(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new LoopMachine();
    }
}
