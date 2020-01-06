<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Exception\ThrowableInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use stdClass;
use Traversable;

/**
 * An exception thrown in relation to a controller.
 *
 * @since [*next-version*]
 */
interface ControllerExceptionInterface extends ThrowableInterface
{
    /**
     * Retrieves the controller that erred.
     *
     * @since [*next-version*]
     *
     * @return ControllerInterface
     */
    public function getController();

    /**
     * Retrieves additional response data.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable
     */
    public function getResponseData();
}
