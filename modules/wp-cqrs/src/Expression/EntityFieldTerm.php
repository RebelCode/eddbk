<?php

namespace RebelCode\Expression;

use Dhii\Expression\VariableTermInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Implementation of a term that represents an entity field.
 *
 * @since [*next-version*]
 */
class EntityFieldTerm extends AbstractTerm implements EntityFieldInterface, VariableTermInterface
{
    /*
     * Provides awareness of an entity name.
     *
     * @since [*next-version*]
     */
    use EntityAwareTrait {
        _getEntity as public getEntity;
    }

    /*
     * Provides awareness of a field name.
     *
     * @since [*next-version*]
     */
    use FieldAwareTrait {
        _getField as public getField;
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
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  The field name.
     * @param string|Stringable $type   The term type.
     */
    public function __construct($entity, $field, $type = '')
    {
        $this->_setEntity($entity);
        $this->_setField($field);
        $this->_setType($type);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        return sprintf('%1$s.%2$s', $this->entity, $this->field);
    }
}
