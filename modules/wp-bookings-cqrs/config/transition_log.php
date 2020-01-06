<?php

return [
    // Config for all transition log RMs
    'table'            => '${cqrs/table_prefix}transition_logs',
    'field_column_map' => $transitionLogFieldColumnMap = [
        'id'         => 'id',
        'transition' => 'transition',
        'date'       => 'date',
        'user_id'    => 'user_id',
        'booking_id' => 'booking_id',
    ],

    // Config for SELECT RMs
    'select' => [
        'tables'           => [
            'transition_log' => '${cqrs/transition_logs/table}',
        ],
        'field_column_map' => $transitionLogFieldColumnMap,
        'joins'            => [],
    ],

    // Config for INSERT RMs
    'insert' => [
        'table'            => '${cqrs/transition_logs/table}',
        'field_column_map' => $transitionLogFieldColumnMap,
        'insert_bulk'      => false,
    ],

    // Config for UPDATE RMs
    'update' => [
        'table'            => '${cqrs/transition_logs/table}',
        'field_column_map' => $transitionLogFieldColumnMap,
    ],

    // Config for DELETE RMs
    'delete' => [
        'table'            => '${cqrs/transition_logs/table}',
        'field_column_map' => $transitionLogFieldColumnMap,
    ],
];
