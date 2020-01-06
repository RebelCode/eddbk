<?php

namespace RebelCode\Expression;

use Dhii\Data\KeyAwareTrait;
use Dhii\Expression\VariableTermInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Implementation of a term that represents an unknown named/keyed value.
 *
 * @since [*next-version*]
 */
class VariableTerm extends AbstractTerm implements VariableTermInterface
{
    /*
     * Provides awareness of a key.
     *
     * @since [*next-version*]'
     */
    use KeyAwareTrait {
        _getKey as public getKey;
    }

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key  The term key; in other words, the name of the variable.
     * @param string|Stringable $type The term type.
     */
    public function __construct($key, $type = '')
    {
        $this->_setKey($key);
        $this->_setType($type);
    }
}
