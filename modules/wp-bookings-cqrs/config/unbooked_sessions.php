<?php

return [
    // Config for all unbooked session RMs
    'table'            => '${cqrs/table_prefix}sessions',
    'field_column_map' => $sessionsFieldColumnMap = [
        'id'           => ['session', 'id'],
        'start'        => ['session', 'start'],
        'end'          => ['session', 'end'],
        'service_id'   => ['session', 'service_id'],
        'resource_ids' => ['session', 'resource_ids'],
        // Temporary solution for "unknown column" errors in SQL queries
        'booking_id'     => ['${cqrs/bookings/table}', 'id'],
        'booking_status' => ['${cqrs/bookings/table}', 'status'],
    ],

    // Config for SELECT RMs
    'select'           => [
        'tables'           => ['session' => '${cqrs/sessions/table}'],
        'field_column_map' => $sessionsFieldColumnMap,
        'joins'            => [
            'unbooked_sessions_select_join_conditions',
        ]
    ],
];
