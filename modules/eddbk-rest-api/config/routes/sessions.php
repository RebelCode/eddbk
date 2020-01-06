<?php

/**
 * This file contains the configuration for the routes related to sessions in EDD Bookings.
 *
 * @since [*next-version*]
 */

return [
    /*
     * The route for querying sessions.
     *
     * @since [*next-version*]
     */
    'get_sessions' => [
        'pattern' => '/sessions',
        'methods' => ['GET'],
        'handler' => 'eddbk_rest_api_query_sessions_handler'
    ],
];
