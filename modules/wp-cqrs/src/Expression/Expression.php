<?php

namespace RebelCode\Expression;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Implementation of a generic expression.
 *
 * @since [*next-version*]
 */
class Expression extends AbstractExpression implements ExpressionInterface
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TermInterface|stdClass|Traversable $terms The expression terms.
     * @param string|Stringable                  $type  The expression type.
     */
    public function __construct($terms = [], $type = '')
    {
        $this->_setTerms($terms);
        $this->_setType($type);
    }
}
