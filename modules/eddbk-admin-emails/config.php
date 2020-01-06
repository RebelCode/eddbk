<?php

return [
    'eddbk_admin_emails' => [
        'edd_email_tags' => [
            'bookings' => [
                'tag'         => 'bookings',
                'description' => 'Information about any purchased bookings',
                'handler'     => 'eddbk_email_bookings_tag_handler',
            ],
        ],
        'templates'      => [
            'receipt'      => [
                'booking_datetime_format' => 'l jS M Y, H:i',
            ],
            'receipt_layout' => [
                'file'          => 'email-receipt.html',
                'token_start'   => '${',
                'token_end'     => '}',
                'token_default' => '',
            ],
        ],
    ],
];
