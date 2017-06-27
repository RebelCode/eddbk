<?php


namespace RebelCode\EddBookings\Core\Modular\Module;
use RebelCode\EddBookings\Core\Modular\Module\AbstractModularPlugin;
use RebelCode\EddBookings\Core\Modular\Module\PluginInterface;

/**
 * Base functionality for a modular plugin.
 *
 * @since[*next-version*]
 */
abstract class AbstractBaseModularPlugin extends AbstractModularPlugin implements PluginInterface
{
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
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        $this->_getKey();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getName()
    {
        return $this->_getName();
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
    public function getContainer()
    {
        return $this->_getContainer();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactory()
    {
        return $this->_getFactory();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function load()
    {
        return $this->_load();
    }
}
