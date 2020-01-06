<?php

/**
 * This file contains the configuration for the EDD Bookings WordPress REST API module.
 *
 * @since [*next-version*]
 */

return [
    'eddbk_rest_api' => [
        /**
         * The REST API version number.
         *
         * @since [*next-version*]
         */
        'version' => '1',

        /**
         * The identifying name of the REST API.
         *
         * @since [*next-version*]
         */
        'name' => 'eddbk',

        /**
         * The REST API namespace.
         *
         * @since [*next-version*]
         */
        'namespace' => '${eddbk_rest_api/name}/v${eddbk_rest_api/version}',

        /**
         * The date time format to use in REST API responses.
         *
         * @since [*next-version*]
         */
        'datetime_format' => DATE_ATOM,

        /*
         * The REST API routes.
         *
         * @since [*next-version*]
         */
        'routes' => array_merge(
            require(__DIR__ . '/routes/bookings.php'),
            require(__DIR__ . '/routes/resources.php'),
            require(__DIR__ . '/routes/sessions.php'),
            require(__DIR__ . '/routes/services.php'),
            require(__DIR__ . '/routes/clients.php')
        ),

        /*
         * Configuration for the REST API controllers.
         *
         * @since [*next-version*]
         */
        'controllers' => [
            /*
             * Configuration for the REST API sessions controller.
             *
             * @since [*next-version*]
             */
            'sessions' => [
                /**
                 * The default number of items to return per page.
                 *
                 * @since [*next-version*]
                 */
                'default_num_sessions_per_page' => 5000,

                /**
                 * The maximum (hard cap) number of items to return per page.
                 *
                 * @since [*next-version*]
                 */
                'max_num_sessions_per_page' => 10000,
            ]
        ],

        /*
         * The WordPress capability that is used to determine if a user is an admin.
         *
         * @since [*next-version*]
         */
        'admin_capability' => 'manage_options',

        /*
         * Configuration for authorization.
         *
         * @since [*next-version*]
         */
        'auth' => [
            /*
             * Configuration for the filter auth validator that uses a nonce stored in a transient.
             *
             * @since [*next-version*]
             */
            'transient_nonce_filter_validator' => [
                /*
                 * The name of the event to trigger.
                 *
                 * @since [*next-version*]
                 */
                'event_name'          => 'eddbk_rest_api_client_auth',

                /*
                 * The key of the event param to filter.
                 *
                 * @since [*next-version*]
                 */
                'event_param_key'     => 'is_authorized',

                /*
                 * The default value of the event param to filter.
                 *
                 * @since [*next-version*]
                 */
                'event_param_default' => false,

                /*
                 * The name of the transient where the nonce is stored.
                 *
                 * @since [*next-version*]
                 */
                'transient_name'      => 'eddbk_rest_api_client_nonce',

                /*
                 * The expiry time of the nonce transient, in seconds.
                 *
                 * @since [*next-version*]
                 */
                'transient_expiry'    => 24 * 60 * 60,

                /*
                 * Configuration for the provided handler of the filter auth validator event.
                 *
                 * @since [*next-version*]
                 */
                'handler' => [
                    /*
                     * The name of the header from where to get the nonce.
                     *
                     * @since [*next-version*]
                     */
                    'header' => 'X-EDDBK-Nonce',

                    /*
                     * The name of the nonce to verify.
                     *
                     * @since [*next-version*]
                     */
                    'nonce'  => 'eddbk_rest_api',
                ],
            ],
        ],
    ],
];
