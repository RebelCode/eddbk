<?php

/**
 * This file contains the configuration for the routes related to resources in the EDD Bookings.
 *
 * @since [*next-version*]
 */

return [
    /*
     * The route for querying resources.
     *
     * @since [*next-version*]
     */
    'get_resources'     => [
        'pattern' => '/resources(?:/(?P<type>[a-zA-Z_]+))?',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_query_resources_handler',
        'authval' => 'eddbk_rest_api_wp_client_app_auth_validator',
    ],

    /*
     * The route for retrieving a resource by ID.
     *
     * @since [*next-version*]
     */
    'get_resource_info' => [
        'pattern' => '/resources(?:/(?P<type>[a-zA-Z_]+))?/(?P<id>[\d]+)',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_get_resource_info_handler',
        'authval' => 'eddbk_rest_api_wp_client_app_auth_validator',
    ],

    /*
     * The route for creating a resource.
     *
     * @since [*next-version*]
     */
    'create_resource'   => [
        'pattern' => '/resources(?:/(?P<type>[a-zA-Z_]+))?',
        'methods' => ['POST'],
        'handler' => 'eddbk_rest_api_create_resource_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for updating a resource.
     *
     * @since [*next-version*]
     */
    'update_resource'   => [
        'pattern' => '/resources(?:/(?P<type>[a-zA-Z_]+))?/(?P<id>[\d]+)',
        'methods' => ['PATCH'],
        'handler' => 'eddbk_rest_api_update_resource_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for updating a resource.
     *
     * @since [*next-version*]
     */
    'delete_resource'   => [
        'pattern' => '/resources(?:/(?P<type>[a-zA-Z_]+))?/(?P<id>[\d]+)',
        'methods' => ['DELETE'],
        'handler' => 'eddbk_rest_api_delete_resource_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],
];
