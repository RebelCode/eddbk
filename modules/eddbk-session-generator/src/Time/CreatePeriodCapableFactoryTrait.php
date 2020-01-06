<?php

namespace RebelCode\EddBookings\Sessions\Time;

use Dhii\Factory\Exception\CouldNotMakeExceptionInterface;
use Dhii\Factory\Exception\FactoryExceptionInterface;
use Dhii\Time\PeriodInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RuntimeException;

/**
 * Provides functionality for creating period instances using a factory.
 *
 * @since [*next-version*]
 */
trait CreatePeriodCapableFactoryTrait
{
    /**
     * Creates a new period instance.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $start The start timestamp.
     * @param int|string|Stringable $end   The end timestamp.
     *
     * @throws RuntimeException If the period factory is null.
     * @throws CouldNotMakeExceptionInterface If the factory failed to make the instance.
     * @throws FactoryExceptionInterface      If an error related to the factory occurs.
     *
     * @return PeriodInterface The created instance.
     */
    protected function _createPeriod($start, $end)
    {
        $factory = $this->_getPeriodFactory();

        if ($factory === null) {
            throw $this->_createRuntimeException(
                $this->__('Cannot create period - the period factory is null'), null, null
            );
        }

        return $factory->make([
            PeriodFactoryInterface::K_START => $start,
            PeriodFactoryInterface::K_END   => $end,
        ]);
    }

    /**
     * Retrieves the period factory associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return PeriodFactoryInterface|null The period factory instance, if any,
     */
    abstract protected function _getPeriodFactory();

    /**
     * Creates a new runtime exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     *
     * @return RuntimeException The new exception.
     */
    abstract protected function _createRuntimeException($message = null, $code = null, $previous = null);

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
