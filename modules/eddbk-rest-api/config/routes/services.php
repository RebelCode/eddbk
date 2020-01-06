<?php

/**
 * This file contains the configuration for the routes related to services in the EDD Bookings.
 *
 * @since [*next-version*]
 */

return [
    /*
     * The route for querying services.
     *
     * @since [*next-version*]
     */
    'get_services' => [
        'pattern' => '/services',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_query_services_handler'
    ],

    /*
     * The route for retrieving a service by ID.
     *
     * @since [*next-version*]
     */
    'get_service_info' => [
        'pattern' => '/services/(?P<id>[\d]+)',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_get_service_info_handler'
    ],

    /*
     * The route for creating a service.
     *
     * @since [*next-version*]
     */
    'create_service'   => [
        'pattern' => '/services',
        'methods' => ['POST'],
        'handler' => 'eddbk_rest_api_create_service_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for updating a service.
     *
     * @since [*next-version*]
     */
    'update_service'   => [
        'pattern' => '/services/(?P<id>[\d]+)',
        'methods' => ['PUT', 'PATCH'],
        'handler' => 'eddbk_rest_api_update_service_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for deleting a service by ID.
     */
    'delete_service' => [
        'pattern' => '/services/(?P<id>[\d]+)',
        'methods' => ['DELETE'],
        'handler' => 'eddbk_rest_api_delete_service_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],
];
