<?php

namespace RebelCode\Expression\Builder;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\InternalException;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * A generic expression builder.
 *
 * This builder will map the name of any public method called for it to an expression factory. The factory should take
 * care to invoke the arguments given to the called builder method, which can be retrieved from the config given to it
 * from index "arguments".
 *
 * @since [*next-version*]
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    /*
     * Provides awareness of a container data store.
     *
     * @since [*next-version*]
     */
    use DataStoreAwareContainerTrait;

    /*
     * Provides functionality for reading data from any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Providers container normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not-found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides functionality for creating internal exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInternalExceptionCapableTrait;

    /*
     * Provides functionality for creating invalid-argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $factories The expression factories.
     */
    public function __construct($factories)
    {
        $this->_setDataStore($factories);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If a problem occurred while invoking a factory.
     */
    public function build($type, $config = [])
    {
        $key    = $this->_getExpressionFactoryKey($type);
        $config = $this->_getExpressionFactoryConfig($config);

        try {
            $factory = $this->_containerGet($this->_getDataStore(), $key);

            return $factory->make($config);
        } catch (RootException $exception) {
            throw $this->_createInternalException(
                $this->__('A problem occurred while trying to map the called method to an expression factory'),
                null,
                $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If a problem occurred while invoking a factory.
     */
    public function __call($name, $arguments)
    {
        return $this->build($name, $arguments);
    }

    /**
     * Retrieves the expression factory key that corresponds the name of the called builder method.
     *
     * @since [*next-version*]
     *
     * @param string $methodName The name of the called method.
     *
     * @return string|Stringable The key that maps to the expression factory.
     */
    protected function _getExpressionFactoryKey($methodName)
    {
        return $methodName;
    }

    /**
     * Retrieves the expression factory config that corresponds to the given set of builder method arguments.
     *
     * @since [*next-version*]
     *
     * @param array $arguments The arguments given to the builder method.
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface The expression factory config.
     */
    protected function _getExpressionFactoryConfig($arguments)
    {
        return [
            'arguments' => $arguments,
        ];
    }
}
