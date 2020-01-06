<?php

namespace RebelCode\Expression;

/**
 * Abstract functionality for an expression.
 *
 * @since [*next-version*]
 */
abstract class AbstractExpression extends AbstractTerm
{
    /*
     * Provides awareness of child terms.
     *
     * @since [*next-version*]
     */
    use TermsAwareTrait {
        _getTerms as public getTerms;
    }
}
