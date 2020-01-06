<?php

namespace RebelCode\Expression\Builder;

use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Something that can build expressions.
 *
 * @since [*next-version*]
 */
interface ExpressionBuilderInterface
{
    /**
     * Builds an expression.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable          $type   The expression type to build.
     * @param array|stdClass|Traversable $config Optional configuration for building.
     *
     * @return TermInterface The build term or expression.
     */
    public function build($type, $config = []);

    /**
     * Builds an expression based on the called method name and the arguments given to it.
     *
     * @see   ExpressionBuilderInterface::build()
     * @since [*next-version*]
     *
     * @param string $name      The called method name - corresponds to the expression type to build.
     * @param array  $arguments The called method's arguments - corresponds to the build configuration.
     *
     * @return TermInterface The built term or expression.
     */
    public function __call($name, $arguments);
}
