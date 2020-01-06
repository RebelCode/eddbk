<?php

/**
 * This file contains the configuration for the routes related to bookings in the EDD Bookings.
 *
 * @since [*next-version*]
 */

return [
    /*
     * The route for querying bookings.
     *
     * @since [*next-version*]
     */
    'get_bookings'     => [
        'pattern' => '/bookings',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_query_bookings_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for retrieving a booking by ID.
     *
     * @since [*next-version*]
     */
    'get_booking_info' => [
        'pattern' => '/bookings/(?P<id>[\d]+)',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_get_booking_info_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for creating a booking.
     *
     * @since [*next-version*]
     */
    'create_booking'   => [
        'pattern' => '/bookings',
        'methods' => ['POST'],
        'handler' => 'eddbk_rest_api_create_booking_handler',
        'authval' => 'eddbk_rest_api_wp_client_app_auth_validator',
    ],

    /*
     * The route for updating a booking.
     *
     * @since [*next-version*]
     */
    'update_booking'   => [
        'pattern' => '/bookings/(?P<id>[\d]+)',
        'methods' => ['PATCH'],
        'handler' => 'eddbk_rest_api_update_booking_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],

    /*
     * The route for updating a booking.
     *
     * @since [*next-version*]
     */
    'delete_booking'   => [
        'pattern' => '/bookings/(?P<id>[\d]+)',
        'methods' => ['DELETE'],
        'handler' => 'eddbk_rest_api_delete_booking_handler',
        'authval' => 'eddbk_rest_api_user_is_admin_auth_validator',
    ],
];
