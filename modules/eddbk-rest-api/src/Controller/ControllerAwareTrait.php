<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Functionality for awareness of a controller.
 *
 * @since [*next-version*]
 */
trait ControllerAwareTrait
{
    /**
     * The controller associated with this instance.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $controller;

    /**
     * Retrieves the controller associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return ControllerInterface The controller instance.
     */
    protected function _getController()
    {
        return $this->controller;
    }

    /**
     * Sets the controller for this instance.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The controller instance.
     *
     * @throws InvalidArgumentException If the argument is not a controller.
     */
    protected function _setController($controller)
    {
        if ($controller !== null && !($controller instanceof ControllerInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a controller instance'), null, null, $controller
            );
        }

        $this->controller = $controller;
    }

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     * @see   _translate()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
