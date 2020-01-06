<?php

namespace RebelCode\Expression;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Provides awareness of a negation flag.
 *
 * @since [*next-version*]
 */
trait NegationAwareTrait
{
    /**
     * The negation flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $negation;

    /**
     * Retrieves the negation.
     *
     * @since [*next-version*]
     *
     * @return bool The negation flag; true if negation is on, false if not.
     */
    protected function _getNegation()
    {
        return $this->negation;
    }

    /**
     * Sets the negation.
     *
     * @since [*next-version*]
     *
     * @param bool $negation The negation flag; true to turn on negation, false otherwise.
     */
    protected function _setNegation($negation)
    {
        if (is_bool($negation)) {
            $this->negation = $negation;

            return;
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Negation flag must be a boolean value'),
            null,
            null,
            $negation
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
