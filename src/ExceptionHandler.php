<?php

namespace RebelCode\EddBookings\Core;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\CallbackAwareTrait;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Invocation\InvokeCallbackCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;

/**
 * Handles exceptions.
 *
 * @since [*next-version*]
 */
class ExceptionHandler implements InvocableInterface
{
    /*
     * Provides awareness of a callback.
     *
     * @since [*next-version*]
     */
    use CallbackAwareTrait;

    /*
     * Provides callback invoking functionality.
     *
     * @since [*next-version*]
     */
    use InvokeCallbackCapableTrait;

    /*
     * Provides callable invoking functionality.
     *
     * @since [*next-version*]
     */
    use InvokeCallableCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * Provides iterable normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIterableCapableTrait;

    /*
     * Provides functionality for creating invocation exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvocationExceptionCapableTrait;

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

    /*
     * The previous exception handler.
     *
     * @since [*next-version*]
     */
    protected $previous;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable $callback The callback to invoke when an exception is handled. The callback will receive the
     *                           exception or PHP7 {@see \Throwable} as argument.
     */
    public function __construct($callback)
    {
        $this->_setCallback($callback);
    }

    /**
     * Registers the handler.
     *
     * @since [*next-version*]
     */
    public function register()
    {
        $this->previous = set_exception_handler($this);
    }

    /**
     * De-registers the handler.
     *
     * @since [*next-version*]
     */
    public function deregister()
    {
        set_exception_handler($this->previous);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        if ($this->previous) {
            $this->_invokeCallable($this->previous, func_get_args());
        }

        $this->_invokeCallback([func_get_arg(0)]);
        call_user_func_array($this->callback, [func_get_arg(0)]);
    }
}
