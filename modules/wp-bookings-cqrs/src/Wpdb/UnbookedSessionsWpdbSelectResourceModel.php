<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\TemplateInterface;
use wpdb;

/**
 * The SELECT resource model for retrieving unbooked sessions.
 *
 * @since [*next-version*]
 */
class UnbookedSessionsWpdbSelectResourceModel extends SessionsSelectResourceModel
{
    /**
     * The internal expression to be added to any consumer-given condition.
     *
     * @since [*next-version*]
     *
     * @var ExpressionInterface
     */
    protected $internalCondition;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface $condition The internal condition to use.
     */
    public function __construct(
        wpdb $wpdb,
        TemplateInterface $template,
        MapFactoryInterface $mapFactory,
        $tables,
        $fieldColumnMap,
        $joins,
        $condition,
        $grouping,
        $exprBuilder
    ) {
        $this->internalCondition = $condition;

        parent::__construct(
            $wpdb,
            $template,
            $mapFactory,
            $tables,
            $fieldColumnMap,
            $exprBuilder,
            $joins,
            $grouping
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function select(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    ) {
        $condition = ($condition !== null)
            ? $this->_getExprBuilder()->and($this->internalCondition, $condition)
            : $this->internalCondition;

        return parent::select($condition, $ordering, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlJoinType(ExpressionInterface $expression)
    {
        return 'LEFT';
    }
}
