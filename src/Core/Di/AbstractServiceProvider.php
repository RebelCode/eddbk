<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Di\AbstractServiceProvider as BaseAbstractServiceProvider;

/**
 * Basic and common functionality for a service provider.
 *
 * @since [*next-version*]
 */
abstract class AbstractServiceProvider extends BaseAbstractServiceProvider
{
    /**
     * The prefix for all service IDs provided by this service provider.
     *
     * To be overridden by extending classes.
     *
     * @since [*next-version*]
     */
    const SERVICE_PREFIX = '';

    /**
     * Parameter-less constructor.
     *
     * Call this in the real constructor.
     *
     * @since[*next-version*]
     */
    protected function _construct()
    {
        $this->_addMany($this->_defaultServices());
    }

    /**
     * Retrieves the default service definitions.
     *
     * @since[*next-version*]
     *
     * @return callable[]|\Traversable A list of service definitions.
     */
    abstract protected function _defaultServices();

    /**
     * Retrieves the prefix for service IDs.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _getServicePrefix()
    {
        return static::SERVICE_PREFIX;
    }

    /**
     * Prefixes a service ID.
     *
     * @since [*next-version*]
     *
     * @param string $serviceId The service ID.
     *
     * @return string The prefixed service ID.
     */
    protected function _p($serviceId)
    {
        return sprintf('%1$s%2$s', $this->_getServicePrefix(), $serviceId);
    }

    /**
     * A shorthand utility method for retrieving a callback to a method of this instance.
     *
     * @since [*next-version*]
     *
     * @param string $methodName The name of the method.
     *
     * @return callable The callback for the method.
     */
    protected function _m($methodName)
    {
        return array($this, $methodName);
    }

    /**
     * Prepares a given list of services by prefixing the service IDs and turning the method names into callables.
     *
     * @since[*next-version*]
     *
     * @param array $services An associative array of method names mapped using unprefixed service IDs.
     *
     * @return callable[] An array of callable service definitions mapped using prefixed service IDs.
     */
    protected function _prepare(array $services)
    {
        $prepared = array();

        foreach ($services as $_sid => $_method) {
            $prepared[$this->_p($_sid)] = $this->_m($_method);
        }

        return $prepared;
    }
}
