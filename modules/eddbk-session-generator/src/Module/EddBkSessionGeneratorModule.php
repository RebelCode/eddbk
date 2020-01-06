<?php

namespace RebelCode\EddBookings\Sessions\Module;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Dhii\Config\ConfigFactoryInterface;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Factory\GenericCallbackFactory;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\Sessions\Generator\DailyRepeatingRule;
use RebelCode\EddBookings\Sessions\Generator\YearlyRepeatingRule;
use RebelCode\EddBookings\Sessions\Time\PeriodFactory;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Sessions\SessionGenerator;

class EddBkSessionGeneratorModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ConfigFactoryInterface    $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If failed to load and read the config file.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RCMOD_EDDBK_SESSION_GENERATOR_CONFIG_FILE),
            $this->_loadPhpConfigFile(RCMOD_EDDBK_SESSION_GENERATOR_SERVICES_FILE)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->_attach('eddbk_generate_sessions', $c->get('eddbk_generate_sessions_handler'));
    }
}
