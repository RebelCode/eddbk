<?php

namespace RebelCode\EddBookings\Core\Di;

use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Di\WritableCompositeContainerInterface;
use Dhii\I18n\StringTranslatingTrait;
use Interop\Container\ContainerInterface as BaseContainerInterface;
use Psr\Container\ContainerInterface;

/**
 * Composite Dependency Injection container for EDD Bookings.
 *
 * @since[*next-version*]
 */
class CompositeContainer implements ContainerInterface, WritableCompositeContainerInterface
{
    use CreateNotFoundExceptionCapableTrait;
    use StringTranslatingTrait;

    /**
     * The children containers.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface[]
     */
    protected $children;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface[] $children The children containers.
     */
    public function __construct($children = [])
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function add(BaseContainerInterface $container)
    {
        $this->children[] = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getContainers()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        for ($i = count($this->children) - 1; $i >= 0; -- $i) {
            $_child = $this->children[$i];

            if ($_child->has($id)) {
                return $_child->get($id);
            }
        }

        throw $this->_createNotFoundException($this->__('Service "%1$s" not found', [$id]), null, null, $this, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        for ($i = count($this->children) - 1; $i >= 0; -- $i) {
            $_child = $this->children[$i];

            if ($_child->has($id)) {
                return true;
            }
        }

        return false;
    }
}
