<?php

namespace RebelCode\Expression;

use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Provides awareness of expression terms.
 *
 * @since [*next-version*]
 */
trait TermsAwareTrait
{
    /**
     * The expression terms.
     *
     * @since [*next-version*]
     *
     * @var TermInterface[]|stdClass|Traversable
     */
    protected $terms;

    /**
     * Retrieves the expression terms.
     *
     * @since [*next-version*]
     *
     * @return TermInterface[]|stdClass|Traversable The expression terms.
     */
    protected function _getTerms()
    {
        return $this->terms;
    }

    /**
     * Sets the expression terms.
     *
     * @since [*next-version*]
     *
     * @param TermInterface[]|stdClass|Traversable $terms The terms to set.
     */
    protected function _setTerms($terms)
    {
        if (is_array($terms) || $terms instanceof stdClass || $terms instanceof Traversable) {
            $this->terms = $terms;

            return;
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Expression terms are not a valid list'),
            null,
            null,
            $terms
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
