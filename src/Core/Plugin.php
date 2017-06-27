<?php


namespace RebelCode\EddBookings\Core;

use Dhii\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\ModuleSystemServiceProvider;

/**
 * Plugin hub class for EDD Bookings.
 *
 * @since[*next-version*]
 */
class Plugin extends AbstractModularPlugin implements PluginInterface
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string             $key       The plugin key.
     * @param ContainerInterface $container The container instance.
     * @param FactoryInterface   $factory   The factory instance.
     */
    public function __construct($key, array $dependencies, array $config, ContainerInterface $container, FactoryInterface $factory)
    {
        $this->_setKey($key)
            ->_setDependencies($dependencies)
            ->_setConfig($config)
            ->_setContainer($container)
            ->_setFactory($factory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        return $this->_getKey();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getDependencies()
    {
        return $this->_getDependencies();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _getConfig()
    {
        return $this->_getConfig();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getModuleLocator()
    {
        return $this->getContainer()->get(ModuleSystemServiceProvider::SID_MODULE_LOCATOR);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getModuleFactory()
    {
        return $this->getContainer()->get(ModuleSystemServiceProvider::SID_MODULE_FACTORY);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
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
    public function load()
    {
        do_action('before_eddbk_load');

        $this->_load();

        do_action('after_eddbk_load');
    }
}
