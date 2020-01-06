<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use wpdb;

/**
 * An insertion resource model for sessions stored in a custom WordPress table.
 *
 * This implementation allows new records to overwrite old ones, if they already exist based on the primary key or a
 * unique index. For sessions, this means that a session with a particular start, end, service and resource combination
 * can overwrite an existing one in the table. This allows newer generated sessions to be inserted (with a different
 * rule ID) without triggering any warnings or errors, while still maintaining the uniqueness of each session.
 *
 * @since [*next-version*]
 */
class SessionsWpdbInsertResourceModel extends AbstractBaseWpdbInsertResourceModel
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb           The WPDB instance to use to prepare and execute queries.
     * @param string|Stringable     $table          The table to insert records into.
     * @param string[]|Stringable[] $fieldColumnMap A map of field names to table column names.
     * @param bool                  $insertBulk     True to insert records in a single bulk query, false to insert them
     *                                              in separate queries.
     */
    public function __construct(wpdb $wpdb, $table, $fieldColumnMap, $insertBulk = true)
    {
        $this->_init($wpdb, $table, $fieldColumnMap, $insertBulk);
    }

    /**
     * {@inheritdoc}
     *
     * Overwrites the original building algorithm to use a REPLACE query instead of an INSERT query.
     * Replace queries are identical to inserts, except that if an old row in the table has the same value as a new row
     * for a PRIMARY KEY or a UNIQUE index, the old row is deleted before the new row is inserted.
     *
     * @link  https://dev.mysql.com/doc/refman/8.0/en/replace.html
     *
     * @since [*next-version*]
     */
    protected function _buildInsertSql($table, $columns, $records, array $valueHashMap = [])
    {
        $original = parent::_buildInsertSql($table, $columns, $records, $valueHashMap);

        $pos = strpos($original, 'INSERT');
        $len = strlen('INSERT');

        $query = substr_replace($original, 'REPLACE', $pos, $len);

        return $query;
    }
}
