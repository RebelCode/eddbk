<?php

namespace RebelCode\EddBookings\Core\Di;

use ArrayAccess;
use Dhii\Cache\ContainerInterface as CacheContainerInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Data\Object\GetDataCapableTrait;
use Dhii\Data\Object\HasDataCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * Dependency Injection container for EDD Bookings.
 *
 * @since[*next-version*]
 */
class Container implements ContainerInterface
{
    use ContainerGetCapableTrait;

    use ContainerHasCapableTrait;

    use GetDataCapableTrait;

    use HasDataCapableTrait;

    use DataStoreAwareContainerTrait;

    use InvokeCallableCapableTrait;

    use NormalizeKeyCapableTrait;

    use NormalizeStringCapableTrait;

    use NormalizeArrayCapableTrait;

    use NormalizeContainerCapableTrait;

    use CreateNotFoundExceptionCapableTrait;

    use CreateContainerExceptionCapableTrait;

    use CreateInvocationExceptionCapableTrait;

    use CreateInvalidArgumentExceptionCapableTrait;

    use CreateOutOfRangeExceptionCapableTrait;

    use StringTranslatingTrait;

    /**
     * The service instance cache.
     *
     * @since [*next-version*]
     *
     * @var CacheContainerInterface
     */
    protected $cache;

    /**
     * The parent container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param CacheContainerInterface                            $cache       The service cache to use.
     * @param callable[]|ArrayAccess|stdClass|ContainerInterface $definitions The service definitions.
     * @param ContainerInterface|null                            $parent      The parent container.
     */
    public function __construct(CacheContainerInterface $cache, $definitions = [], ContainerInterface $parent = null)
    {
        $this->cache = $cache;
        $this->parent = $parent;

        $this->_setDataStore($definitions);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        return $this->cache->get(
            $key,
            function($key, $ttl) {
                return $this->_createService($key);
            }
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return $this->_hasData($id);
    }

    /**
     * Creates a new service instance based on its definition.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key The key of the service to create.
     *
     * @return mixed The created service instance.
     *
     * @throws InvalidArgumentException    If key is invalid.
     * @throws InvalidArgumentException    If the service definition is an invalid callable.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     * @throws InvocationExceptionInterface For errors that happen during invocation.
     */
    protected function _createService($key)
    {
        $definition = $this->_getData($key);
        $container = $this->parent === null? $this : $this->parent;
        $arguments = [$container];

        return $this->_invokeCallable($definition, $arguments);
    }
}
