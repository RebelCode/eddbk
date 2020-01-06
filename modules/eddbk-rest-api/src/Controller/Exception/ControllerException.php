<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Exception\AbstractBaseException;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use Traversable;

/**
 * An exception related to a controller.
 *
 * @since [*next-version*]
 */
class ControllerException extends AbstractBaseException implements ControllerExceptionInterface
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /* @since [*next-version*] */
    use DataStoreAwareContainerTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __construct(
        $message = null,
        $code = null,
        $previous = null,
        ControllerInterface $controller = null,
        $data = []
    ) {
        $this->_initParent($message, $code, $previous);
        $this->_setController($controller);

        if ($data instanceof Traversable) {
            $data = $this->_normalizeArray($data);
        }

        $this->_setDataStore($this->_normalizeIterable($data));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getController()
    {
        return $this->_getController();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getResponseData()
    {
        return $this->_getDataStore();
    }
}
