<?php

namespace RebelCode\EddBookings\Core;

use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\CallbackAwareTrait;
use Dhii\Invocation\InvocableInterface;
use Exception;
use Throwable;

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
     * The root directory for which to limit exception handling.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string   $rootDir  The root directory for which to limit exception handling.
     * @param callable $callback The callback to invoke when an exception is handled. The callback will receive the
     *                           exception or PHP7 {@see \Throwable} as argument.
     */
    public function __construct($rootDir, $callback)
    {
        $this->rootDir = $rootDir;
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
            call_user_func_array($this->previous, func_get_args());
        }

        $throwable = func_get_arg(0);

        if (!($throwable instanceof Exception) && !($throwable instanceof Throwable)) {
            return;
        }

        // Detect an exception thrown from within the root directory
        foreach ($throwable->getTrace() as $_trace) {
            if (stripos($_trace['file'], $this->rootDir) === 0) {
                 call_user_func_array($this->_getCallback(), [$throwable]);

                 return;
            }
        }
    }
}
