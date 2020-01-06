<?php

/**
 * This file contains the configuration for the routes related to clients in the EDD Bookings.
 *
 * @since [*next-version*]
 */

return [
    /*
     * The route for querying clients.
     *
     * @since [*next-version*]
     */
    'get_clients' => [
        'pattern' => '/clients',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_query_clients_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for retrieving a client by ID.
     *
     * @since [*next-version*]
     */
    'get_client_info' => [
        'pattern' => '/clients/(?P<id>[\d]+)',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_get_client_info_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for retrieving a client by ID.
     *
     * @since [*next-version*]
     */
    'create_client' => [
        'pattern' => '/clients',
        'methods' => ['POST'],
        'handler' => 'eddbk_rest_api_create_client_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],
];
