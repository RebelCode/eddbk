<?php

return [
    // Config for all session rule RMs
    'table'            => '${cqrs/table_prefix}session_rules',
    'field_column_map' => $sessionRulesFieldColumnMap = [
        'id'                  => 'id',
        'resource_id'         => 'resource_id',
        'start'               => 'start',
        'end'                 => 'end',
        'all_day'             => 'all_day',
        'repeat'              => 'repeat',
        'repeat_period'       => 'repeat_period',
        'repeat_unit'         => 'repeat_unit',
        'repeat_until'        => 'repeat_until',
        'repeat_until_date'   => 'repeat_until_date',
        'repeat_until_period' => 'repeat_until_period',
        'repeat_weekly_on'    => 'repeat_weekly_on',
        'repeat_monthly_on'   => 'repeat_monthly_on',
        'exclude_dates'       => 'exclude_dates',
    ],

    // Config for SELECT RMs
    'select'           => [
        'tables'           => ['session_rule' => '${cqrs/session_rules/table}'],
        'field_column_map' => $sessionRulesFieldColumnMap,
        'joins'            => [],
    ],

    // Config for INSERT RMs
    'insert'           => [
        'table'            => '${cqrs/session_rules/table}',
        'field_column_map' => $sessionRulesFieldColumnMap,
        'insert_bulk'      => true,
    ],

    // Config for UPDATE RMs
    'update'           => [
        'table'            => '${cqrs/session_rules/table}',
        'field_column_map' => $sessionRulesFieldColumnMap,
    ],

    // Config for DELETE RMs
    'delete'           => [
        'table'            => '${cqrs/session_rules/table}',
        'field_column_map' => $sessionRulesFieldColumnMap,
    ],
];
