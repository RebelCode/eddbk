<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Di\Exception\ContainerException;
use Interop\Container\ServiceProvider;

/**
 * Base functionality for service providers.
 *
 * @since[*next-version*]
 */
abstract class AbstractBaseServiceProvider extends AbstractServiceProvider implements ServiceProvider
{
    /**
     * Constructor.
     *
     * @since[*next-version*]
     *
     */
    public function __construct()
    {
        $this->_construct();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServices()
    {
        return $this->_getServices();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createContainerException($message, $code = 0, \Exception $innerException = null)
    {
        return new ContainerException($message, $code, $innerException);
    }
}
