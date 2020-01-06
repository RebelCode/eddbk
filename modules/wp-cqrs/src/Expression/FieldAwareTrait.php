<?php

namespace RebelCode\Expression;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Provides awareness of a field name.
 *
 * @since [*next-version*]
 */
trait FieldAwareTrait
{
    /**
     * The field name.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable|null
     */
    protected $field;

    /**
     * Retrieves the field name.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable|null The field name.
     */
    protected function _getField()
    {
        return $this->field;
    }

    /**
     * Sets the field name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $field The field name to set.
     */
    protected function _setField($field)
    {
        if ($field === null || is_string($field) || $field instanceof Stringable) {
            $this->field = $field;

            return;
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Field must be a valid string or stringable argument'),
            null,
            null,
            $field
        );
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
