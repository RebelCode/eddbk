<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Factory\FactoryAwareTrait;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Controller\Exception\ControllerExceptionInterface;
use RebelCode\EddBookings\RestApi\Controller\Exception\CreateControllerExceptionCapableTrait;
use stdClass;
use Traversable;

/**
 * Base functionality for API controllers.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseController implements ControllerInterface
{
    /* @since [*next-version*] */
    use CreateResultsIteratorCapableFactoryTrait;

    /* @since [*next-version*] */
    use FactoryAwareTrait;

    /* @since [*next-version*] */
    use CreateControllerExceptionCapableTrait;

    /**
     * The last received request params.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|ArrayAccess|ContainerInterface
     */
    protected $params;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($params = [])
    {
        $this->params = $params;

        return $this->_createResultsIterator($this->_get($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function post($params = [])
    {
        $this->params = $params;

        return $this->_createResultsIterator($this->_post($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function put($params = [])
    {
        $this->params = $params;

        return $this->_createResultsIterator($this->_put($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function patch($params = [])
    {
        $this->params = $params;

        return $this->_createResultsIterator($this->_patch($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function delete($params = [])
    {
        $this->params = $params;

        return $this->_createResultsIterator($this->_delete($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getResultsIteratorFactory($results)
    {
        return $this->_getFactory();
    }

    /**
     * Retrieves resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    abstract protected function _get($params = []);

    /**
     * Creates resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements:
     *                                    array, {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    abstract protected function _post($params = []);

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
    abstract protected function _put($params = []);

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
    abstract protected function _patch($params = []);

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
    abstract protected function _delete($params = []);
}
