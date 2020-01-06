<?php

namespace RebelCode\Expression;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Expression\TermInterface;
use Dhii\I18n\StringTranslatingTrait;

/**
 * Abstract functionality for expressions and terms.
 *
 * @since [*next-version*]
 */
abstract class AbstractTerm implements TermInterface
{
    /*
     * Provides awareness of an expression type.
     */
    use TypeAwareTrait {
        _getType as public getType;
    }

    /*
     * Provides functionality for creating invalid-argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;
}
