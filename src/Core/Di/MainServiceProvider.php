<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Machine\LoopMachine;
use Interop\Container\ContainerInterface;
use RebelCode\EddBookings\Core\I18n\WpTranslator;
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
     * The service ID of the I18n component.
     *
     * @since [*next-version*]
     */
    const SID_I18N = 'i18n';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServices()
    {
        return $this->_prepare(array(
            static::SID_PLUGIN       => 'getPlugin',
            static::SID_FACTORY      => 'getFactory',
            static::SID_LOOP_MACHINE => 'getLoopMachine',
            static::SID_I18N         => 'getI18n',
        ));
    }

    /**
     * Service definition for the plugin hub instance.
     *
     * @since[*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return ContainerInterface
     */
    public function getPlugin(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new Plugin(EDDBK_SLUG, array(), array(), $c, $c->get(static::SID_FACTORY));
    }

    /**
     * Service definition for a generic factory.
     *
     * @since[*next-version*]
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
     * @since[*next-version*]
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

    /**
     * Service definition for an internationalization translator.
     *
     * @since[*next-version*]
     *
     * @param ContainerInterface $c        The container instance.
     * @param mixed              $previous The previous instance, if any. Default: null
     * @param array              $config   Any configuration data. Default: array()
     *
     * @return WpTranslator
     */
    public function getI18n(ContainerInterface $c, $previous = null, array $config = array())
    {
        return new WpTranslator(EDDBK_TEXT_DOMAIN);
    }
}
