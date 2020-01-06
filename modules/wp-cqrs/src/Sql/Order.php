<?php

namespace RebelCode\Storage\Resource\Sql;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\Sql\OrderInterface;
use RebelCode\Expression\EntityAwareTrait;
use RebelCode\Expression\FieldAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;

class Order implements OrderInterface
{
    /*
     * @since [*next-version*]
     */
    use EntityAwareTrait;

    /*
     * @since [*next-version*]
     */
    use FieldAwareTrait;

    /*
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Ascending flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $ascending;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $entity    The name of the field's entity, if any.
     * @param string|Stringable      $field     The name of the field to sort by.
     * @param bool                   $ascending True for ascending, false for descending.
     */
    public function __construct($entity, $field, $ascending)
    {
        $this->_setEntity($entity);
        $this->_setField($field);
        $this->ascending = $ascending;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEntity()
    {
        return $this->_getEntity();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getField()
    {
        return $this->_getField();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]'
     */
    public function isAscending()
    {
        return $this->ascending;
    }
}
