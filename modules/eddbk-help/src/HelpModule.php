<?php

namespace RebelCode\EddBookings\Help;

use ArrayAccess;
use Dhii\Config\ConfigFactoryInterface;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;
use stdClass;

/**
 * The EDD Bookings help module.
 *
 * @since [*next-version*]
 */
class HelpModule extends AbstractBaseModule
{
    /**
     * The module config.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|ArrayAccess|ContainerInterface
     */
    protected $config;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $config               The module config.
     * @param ConfigFactoryInterface                        $configFactory        The config factory.
     * @param ContainerFactoryInterface                     $containerFactory     The container factory.
     * @param ContainerFactoryInterface                     $compContainerFactory The composite container factory.
     * @param EventManagerInterface                         $eventManager         The event manager.
     * @param EventFactoryInterface                         $eventFactory         The event factory.
     */
    public function __construct(
        $config,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $key          = $this->_containerGet($config, 'key');
        $dependencies = $this->_containerHas($config, 'dependencies')
            ? $this->_containerGet($config, 'dependencies')
            : [];
        $this->config = $config;
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If an error occurred while reading from the config or services files.
     */
    public function setup()
    {
        $configFile   = $this->_containerGet($this->config, 'config_file_path');
        $servicesFile = $this->_containerGet($this->config, 'services_file_path');

        return $this->_setupContainer(
            $this->_createCompositeContainer([
                $this->_createConfig([$this->key => $this->config]),
                $this->_createConfig($this->_loadPhpConfigFile($configFile)),
            ]),
            $this->_loadPhpConfigFile($servicesFile)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->_attach('admin_enqueue_scripts', $c->get('eddbk_enqueue_beacon_handler'));
        $this->_attach('admin_enqueue_scripts', $c->get('eddbk_hide_wp_version_handler'));
    }
}
