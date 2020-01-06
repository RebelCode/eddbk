<?php

/**
 * This file contains the configuration for the EDD Bookings WordPress CQRS Module.
 *
 * @since [*next-version*]
 */

return [
    'cqrs' => [
        /*
         * Modifies the table prefix to also include a plugin-specific prefix.
         *
         * @since [*next-version*]
         */
        'table_prefix' => '${wpdb_prefix}eddbk_',
    ],

    /*
     * Override some generic wp_bookings config.
     *
     * @since [*next-version*]
     */
    'wp_bookings_cqrs' => [
        'migrations' => [
            /*
             * Override the generic DB version option name to be specific to EDD Bookings.
             *
             * @since [*next-version*]
             */
            'db_version_option' => 'eddbk_bookings_db_version'
        ]
    ]
];
