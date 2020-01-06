<?php

return [
    'bookings_front_ui' => [
        /*
         * Configuration for placeholder templates.
         */
        'templates_config' => [
            'token_start'   => '${',
            'token_end'     => '}',
            'token_default' => '',
        ],

        'state_var_name'       => 'EDDBK_WIZARD_APP_STATE',
        'application_selector' => '[data-eddbk-wizard-holder]',
        'api_endpoint_urls'    => [
            'bookings' => '/eddbk/v1/bookings/',
            'services' => '/eddbk/v1/services/',
            'sessions' => '/eddbk/v1/sessions/',
        ],
        'initial_booking_transition' => 'cart',
        'formats'                    => [
            'datetime' => [
                'sessionTime'      => 'h:mm a',
                'dayFull'          => 'dddd Do MMMM YYYY',
                'dayShort'         => 'D MMMM YYYY',
                'dayKey'           => 'YYYY-MM-DD',
                'appointmentStart' => 'h:mm a \o\\n dddd Do MMMM YYYY',
            ],
        ],
        /*
         * Data map for booking resource. Key field name for using on the client.
         * Value is is real key name which be sent to the API.
         *
         * @since [*next-version*]
         */
        'booking_data_map' => [
            'notes'    => 'notes',
            'timezone' => 'clientTz',
        ],

        'assets_urls_map_path' => RC_BOOKINGS_FRONT_UI_MODULE_CONFIG_DIR . '/assets_urls_map.php',
        'assets'               => [
            'wizard' => [
                'app.min.js' => 'wizard_ui/dist/app.min.js',
            ],
            'styles' => [
                'app' => 'wizard_ui/dist/app.min.css',
            ],
        ],
    ],
];
