<?php

namespace RebelCode\EddBookings\Sessions\Time;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Provides functionality for awareness of a period factory instance.
 *
 * @since [*next-version*]
 */
trait PeriodFactoryAwareTrait
{
    /**
     * The period factory to use to create session generation periods.
     *
     * @since [*next-version*]
     *
     * @var PeriodFactoryInterface|null
     */
    protected $periodFactory;

    /**
     * Retrieves the period factory associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return PeriodFactoryInterface|null The period factory instance, if any,
     */
    protected function _getPeriodFactory()
    {
        return $this->periodFactory;
    }

    /**
     * Sets the period factory for this instance.
     *
     * @since [*next-version*]
     *
     * @param PeriodFactoryInterface|null $periodFactory The period factory instance, if any.
     *
     * @throws InvalidArgumentException If the argument is not a period factory instance.
     */
    protected function _setPeriodFactory($periodFactory)
    {
        if ($periodFactory !== null && !($periodFactory instanceof PeriodFactoryInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a period factory instance'), null, null, $periodFactory
            );
        }

        $this->periodFactory = $periodFactory;
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
