<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;
use wpdb;

/**
 * A specialized resource model for retrieving booking statuses.
 *
 * @since [*next-version*]
 */
class BookingStatusWpdbSelectResourceModel extends AbstractBaseWpdbSelectResourceModel
{
    /**
     * The columns to group by.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable
     */
    protected $groupColumns;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                         $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface            $expressionTemplate The template for rendering SQL expressions.
     * @param MapFactoryInterface          $mapFactory         The factory that creates maps, for the returned records.
     * @param array|stdClass|Traversable   $tables             The tables names (values) mapping to their aliases (keys)
     *                                                         or null for no aliasing.
     * @param string[]|Stringable[]        $fieldColumnMap     A map of field names to table column names.
     * @param array|Traversable            $groupColumns       The column names to group by.
     * @param LogicalExpressionInterface[] $joins              A list of JOIN expressions to use in SELECT queries.
     */
    public function __construct(
        wpdb $wpdb,
        TemplateInterface $expressionTemplate,
        MapFactoryInterface $mapFactory,
        $tables,
        $fieldColumnMap,
        $groupColumns,
        $joins = []
    ) {
        $this->_init($wpdb, $expressionTemplate, $mapFactory, $tables, $fieldColumnMap, $joins);
        $this->_setGroupColumns($groupColumns);
    }

    /**
     * Retrieves the list of columns to group by.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable The list of columns.
     */
    protected function _getGroupColumns()
    {
        return $this->groupColumns;
    }

    /**
     * Sets the list of columns to group by.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $groupColumns The list of columns.
     */
    protected function _setGroupColumns($groupColumns)
    {
        $this->groupColumns = $groupColumns;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _buildSqlWhereClause(
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    ) {
        $result = '';

        $groupColumns = $this->_escapeSqlReferenceList($this->_getGroupColumns());
        $result       .= sprintf('GROUP BY %s', $groupColumns);

        if ($condition !== null) {
            $rendered = $this->_renderSqlCondition($condition, $valueHashMap);
            $rendered = $this->_normalizeString($rendered);

            $result .= ' HAVING ' . $rendered;
        }

        return $result;
    }
}
