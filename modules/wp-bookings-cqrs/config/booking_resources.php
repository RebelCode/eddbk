<?php

return [
    // Config for all booking-resource RMs
    'table'            => '${cqrs/table_prefix}booking_resources',
    'field_column_map' => $bookingResourcesFcMap = [
        'id'          => 'id',
        'booking_id'  => 'booking_id',
        'resource_id' => 'resource_id',
    ],

    // Config for SELECT RMs
    'select'           => [
        'tables'           => [
            'booking_resource' => '${cqrs/booking_resources/table}',
        ],
        'field_column_map' => $bookingResourcesFcMap,
        'joins'            => [],
    ],

    // Config for INSERT RMs
    'insert'           => [
        'table'            => '${cqrs/booking_resources/table}',
        'field_column_map' => $bookingResourcesFcMap,
        'insert_bulk'      => true,
    ],

    // Config for UPDATE RMs
    'update'           => [
        'table'            => '${cqrs/booking_resources/table}',
        'field_column_map' => $bookingResourcesFcMap,
    ],

    // Config for DELETE RMs
    'delete'           => [
        'table'            => '${cqrs/booking_resources/table}',
        'field_column_map' => $bookingResourcesFcMap,
    ],
];
