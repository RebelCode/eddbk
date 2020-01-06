<?php

namespace RebelCode\Expression;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Implementation of a logical expression.
 *
 * @since [*next-version*]
 */
class LogicalExpression extends AbstractExpression implements LogicalExpressionInterface
{
    /*
     * Provides awareness of a negation flag.
     *
     * @since [*next-version*]
     */
    use NegationAwareTrait {
        _getNegation as public isNegated;
    }

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TermInterface[]|stdClass|Traversable $terms    The expression terms.
     * @param bool                                 $negation True if the expression is negated, false if not.
     * @param string|Stringable                    $type     the expression type.
     */
    public function __construct($terms = [], $negation = false, $type = '')
    {
        $this->_setTerms($terms);
        $this->_setNegation($negation);
        $this->_setType($type);
    }
}
