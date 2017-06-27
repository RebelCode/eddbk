<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\Core\Di\ModuleSystemServiceProvider;
use RebelCode\EddBookings\Core\Modular\Module\AbstractBaseModularPlugin;

/**
 * Plugin hub class for EDD Bookings.
 *
 * @since[*next-version*]
 */
class Plugin extends AbstractBaseModularPlugin
{
    /**
     * The plugin slug.
     *
     * @since [*next-version*]
     */
    const PLUGIN_SLUG = 'eddbk/eddbk.php';

    /**
     * The user-friendly plugin name.
     *
     * @since [*next-version*]
     */
    const PLUGIN_NAME = 'EDD Bookings';

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
        $this->_setKey(static::PLUGIN_SLUG)
            ->_setName(static::PLUGIN_NAME)
            ->_setDependencies($this->_getDefaultDependencies())
            ->_setConfig($this->_getDefaultConfig())
            ->_setContainer($container)
            ->_setFactory($factory);

        $this->_construct();
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
     * Retrieves the default dependencies.
     *
     * @since[*next-version*]
     *
     * @return array
     */
    protected function _getDefaultDependencies()
    {
        return array(
            $this->_createDependency(
                'easy-digital-downloads/easy-digital-downloads.php',
                'Easy Digital Downloads',
                '2.3.0'
            ),
        );
    }

    /**
     * Retrieves the default configuration.
     *
     * @since[*next-version*]
     *
     * @return array
     */
    protected function _getDefaultConfig()
    {
        return array();
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
