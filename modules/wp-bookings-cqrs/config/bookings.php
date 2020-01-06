<?php

return [
    // Config for all booking RMs
    'table'            => '${cqrs/table_prefix}bookings',
    'field_column_map' => $bookingsFieldColumnMap = [
        'id'          => ['booking', 'id'],
        'start'       => ['booking', 'start'],
        'end'         => ['booking', 'end'],
        'service_id'  => ['booking', 'service_id'],
        'payment_id'  => ['booking', 'payment_id'],
        'client_id'   => ['booking', 'client_id'],
        'client_tz'   => ['booking', 'client_tz'],
        'admin_notes' => ['booking', 'admin_notes'],
        'status'      => ['booking', 'status'],
    ],

    // Config for SELECT RMs
    'select'           => [
        'tables'           => [
            'booking' => '${cqrs/bookings/table}',
        ],
        'field_column_map' => $bookingsFieldColumnMap,
        'joins'            => [
            'bookings_select_rm_resources_join'
        ],
    ],

    // Config for INSERT RMs
    'insert'           => [
        'table'            => '${cqrs/bookings/table}',
        'field_column_map' => [
            'id'          => 'id',
            'start'       => 'start',
            'end'         => 'end',
            'service_id'  => 'service_id',
            'payment_id'  => 'payment_id',
            'client_id'   => 'client_id',
            'client_tz'   => 'client_tz',
            'admin_notes' => 'admin_notes',
            'status'      => 'status',
        ],
        'insert_bulk'      => false,
    ],

    // Config for UPDATE RMs
    'update'           => [
        'table'            => '${cqrs/bookings/table}',
        'field_column_map' => $bookingsFieldColumnMap,
    ],

    // Config for DELETE RMs
    'delete'           => [
        'table'            => '${cqrs/bookings/table}',
        'field_column_map' => $bookingsFieldColumnMap,
    ],
];
