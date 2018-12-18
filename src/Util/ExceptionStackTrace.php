<?php

namespace RebelCode\EddBookings\Core\Util;

use Dhii\Util\String\StringableInterface;
use Exception;
use Throwable;

/**
 * Represents an exception's stack trace and provides functionality for outputting the stack trace with more detail than
 * PHP would by default via {@see Exception::getTraceAsString}.
 *
 * Based on `jTraceEx()` by `ernest@vogelsinger.at`.
 *
 * @since 0.1
 */
class ExceptionStackTrace implements StringableInterface
{
    /**
     * The exception instance.
     *
     * @since 0.1
     *
     * @var Exception
     */
    protected $exception;

    /**
     * Constructor.
     *
     * @since 0.1
     *
     * @param Exception|Throwable $exception The exception instance.
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Provides a more detailed exception trace than PHP.
     *
     * @param Exception|Throwable $exception The exception whose trace to print.
     * @param array|null          $seen      Array passed to recursive calls to accumulate trace lines already seen
     *                                       leave as NULL when calling this function
     *
     * @return string The exception trace.
     */
    protected function _stackTrace($exception, $seen = null)
    {
        $starter = $seen ? "Caused by:\n" : '';

        if ($seen === null) {
            $seen = [];
        }

        $trace = $exception->getTrace();
        $prev  = $exception->getPrevious();
        $file  = $exception->getFile();
        $line  = $exception->getLine();

        $result   = [];
        $result[] = sprintf('%s%s: "%s"', $starter, get_class($exception), $exception->getMessage());

        while (count($trace)) {
            $currTrace = array_shift($trace);
            $current   = "$file:$line";

            if (count($seen) && in_array($current, $seen)) {
                $result[] = sprintf("\t" . '... %d more', count($trace) + 1);
                break;
            }

            $class = array_key_exists('class', $currTrace)
                ? $currTrace['class']
                : '';
            $function = array_key_exists('function', $currTrace)
                ? $currTrace['function'] . '()'
                : '{main}';
            $classFuncSep = ($class !== '')
                ? '::'
                : '';
            $routine = $class . $classFuncSep . $function;
            $source  = ($line === null)
                ? $file
                : basename($file) . ':' . $line;

            $result[] = sprintf("\t" . 'at %1$s [%2$s]', $routine, $source);
            $seen[]   = "$file:$line";

            $file = array_key_exists('file', $currTrace)
                ? $currTrace['file']
                : 'Unknown Source';
            $line = (array_key_exists('line', $currTrace) && $currTrace['line'])
                ? $currTrace['line']
                : null;
        }

        $result = implode("\n", $result);

        if ($prev) {
            $result .= "\n\n" . $this->_stackTrace($prev, $seen);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @since 0.1
     */
    public function __toString()
    {
        return $this->_stackTrace($this->exception);
    }
}
