<?php

namespace RebelCode\Expression;

use Dhii\Data\ValueAwareTrait;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Implementation of a term that represents a literal value.
 *
 * @since [*next-version*]
 */
class LiteralTerm extends AbstractTerm implements LiteralTermInterface
{
    /*
     * Provides awareness of a value.
     *
     * @since [*next-version*]
     */
    use ValueAwareTrait {
        _getValue as public getValue;
    }

    /*
     * The default type.
     *
     * @since [*next-version*]
     */
    const DEFAULT_TYPE = 'literal';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $value The term value.
     * @param string|Stringable $type  The term type.
     */
    public function __construct($value, $type = self::DEFAULT_TYPE)
    {
        $this->_setValue($value);
        $this->_setType($type);
    }
}
