<?php

namespace RebelCode\Expression\Builder;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Provides awareness of an expression builder.
 *
 * @since [*next-version*]
 */
trait ExpressionBuilderAwareTrait
{
    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var ExpressionBuilderInterface|null
     */
    protected $expressionBuilder;

    /**
     * Retrieves the expression builder associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return ExpressionBuilderInterface|null The expression builder instance, if any.
     */
    protected function _getExpressionBuilder()
    {
        return $this->expressionBuilder;
    }

    /**
     * Sets the expression builder for this instance.
     *
     * @since [*next-version*]
     *
     * @param ExpressionBuilderInterface|null $expressionBuilder The expression builder instance, if any.
     */
    protected function _setExpressionBuilder($expressionBuilder)
    {
        if ($expressionBuilder !== null && !($expressionBuilder instanceof ExpressionBuilderInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an expression builder'), null, null, $expressionBuilder
            );
        }

        $this->expressionBuilder = $expressionBuilder;
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
