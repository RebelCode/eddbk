<?php

namespace RebelCode\EddBookings\Core\Di;

use Interop\Container\ServiceProvider;

abstract class AbstractBaseServiceProvider extends AbstractServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServices()
    {
        return $this->_getServices();
    }
}
