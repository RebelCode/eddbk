<?php

/**
 * This file contains the configuration for the WP Bookings CQRS Module.
 *
 * @since [*next-version*]
 */

return [
    'cqrs'             => [
        'table_prefix'      => '${wpdb_prefix}',
        'bookings'          => include __DIR__ . '/bookings.php',
        'sessions'          => include __DIR__ . '/sessions.php',
        'unbooked_sessions' => include __DIR__ . '/unbooked_sessions.php',
        'session_rules'     => include __DIR__ . '/session_rules.php',
        'transition_logs'   => include __DIR__ . '/transition_log.php',
        'resources'         => include __DIR__ . '/resources.php',
        'booking_resources' => include __DIR__ . '/booking_resources.php',
    ],
    'wp_bookings_cqrs' => [
        'migrations' => [
            /*
             * The target version to migrate to.
             *
             * @since [*next-version*]
             */
            'target_db_version'         => 3,

            /*
             * The WordPress option name where the database version is saved.
             *
             * @since [*next-version*]
             */
            'db_version_option'         => 'wp_bookings_db_version',

            /*
             * The starting delimiter of placeholder tokens.
             *
             * @since [*next-version*]
             */
            'placeholder_token_start'   => '${',

            /*
             * The ending delimiter of placeholder tokens.
             *
             * @since [*next-version*]
             */
            'placeholder_token_end'     => '}',

            /*
             * The default value to use when a placeholder token does not map to a value.
             *
             * @since [*next-version*]
             */
            'placeholder_default_value' => '',
        ],
    ],
];
