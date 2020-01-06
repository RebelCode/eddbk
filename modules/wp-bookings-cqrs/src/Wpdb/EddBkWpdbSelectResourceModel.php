<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;
use wpdb;

/**
 * An extension of the generic WPDB select resource model.
 *
 * This implementations adds normalization for the array columns in the field-column maps to entity-field instances and
 * the ability to inject grouping.
 *
 * @since [*next-version*]
 */
class EddBkWpdbSelectResourceModel extends WpdbSelectResourceModel
{
    /* @since [*next-version*] */
    use NormalizeSqlFieldColumnMapCapableTrait;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $expBuilder;

    /**
     * The fields to group by.
     *
     * @since [*next-version*]
     *
     * @var array<string|Stringable|EntityFieldInterface>|stdClass|Traversable
     */
    protected $grouping;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param object                                                            $expBuilder The SQL expression builder.
     * @param string[]|Stringable[]|EntityFieldInterface[]|stdClass|Traversable $grouping   The fields to group by.
     */
    public function __construct(
        wpdb $wpdb,
        TemplateInterface $expressionTemplate,
        MapFactoryInterface $factory,
        $tables,
        $fieldColumnMap,
        $expBuilder,
        $joins = [],
        $grouping = []
    ) {
        $this->expBuilder = $expBuilder;
        $this->grouping   = $grouping;

        parent::__construct($wpdb, $expressionTemplate, $factory, $tables, $fieldColumnMap, $joins);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getExprBuilder()
    {
        return $this->expBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectGrouping()
    {
        return $this->grouping;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _setSqlFieldColumnMap($map)
    {
        parent::_setSqlFieldColumnMap($this->_normalizeSqlFieldColumnMap($map));
    }
}
