<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Controller\Exception\ControllerExceptionInterface;
use stdClass;
use Traversable;

/**
 * An API controller - something that can work with API resources.
 *
 * @since [*next-version*]
 */
interface ControllerInterface
{
    /**
     * Retrieves resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @throws ControllerExceptionInterface If an error occurred.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function get($params = []);

    /**
     * Creates resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @throws ControllerExceptionInterface If an error occurred.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function post($params = []);

    /**
     * Updates resources entirely, based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @throws ControllerExceptionInterface If an error occurred.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function put($params = []);

    /**
     * Modifies resources, based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @throws ControllerExceptionInterface If an error occurred.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function patch($params = []);

    /**
     * Deletes resources, based on parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @throws ControllerExceptionInterface If an error occurred.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function delete($params = []);
}
