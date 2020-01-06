<?php

namespace RebelCode\Expression;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Provides awareness of an expression type.
 *
 * @since [*next-version*]
 */
trait TypeAwareTrait
{
    /**
     * The term type.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $type;

    /**
     * Sets the expression type.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable
     */
    protected function _getType()
    {
        return $this->type;
    }

    /**
     * Retrieves the expression type.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $type The expression type.
     */
    protected function _setType($type)
    {
        if (!is_string($type) && !($type instanceof Stringable)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Expression type must be a stringable argument'),
                null,
                null,
                $type
            );
        }

        $this->type = $type;
    }

    /**
     * Creates a new invalid argument exception.
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
