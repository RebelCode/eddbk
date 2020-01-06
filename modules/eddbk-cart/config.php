<?php

return [
    /*
     * Config for the EDDBK cart module.
     *
     * @since [*next-version*]
     */
    'eddbk_cart' => [
        /*
         * Config for cart items.
         *
         * @since [*next-version*]
         */
        'cart_items' => [
            /*
             * Config the cart item data.
             *
             * @since [*next-version*]
             */
            'data' => [
                /*
                 * The EDD key where cart item data is stored for cart items.
                 *
                 * @since [*next-version*]
                 */
                'key' => 'options',

                /*
                 * The key used in cart item data to store the price option ID.
                 *
                 * @since [*next-version*]
                 */
                'price_id_key' => 'price_id',

                /*
                 * The main key used in cart item data by EDD Bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_key' => 'eddbk',

                /*
                 * The key used in cart item data to store the booking ID.
                 *
                 * @since [*next-version*]
                 */
                'booking_id_key' => 'booking_id',
            ],
            /*
             * The datetime format to use when rendering booking dates and times in the cart.
             *
             * @since [*next-version*]
             */
            'booking_datetime_format' => 'D, jS M Y, H:i',

            /*
             * Templates used in the cart.
             *
             * @since [*next-version*]
             */
            'templates' => [
                /*
                 * The template for booking info in the cart.
                 *
                 * @since [*next-version*]
                 */
                'booking_info' => 'cart-booking-info.html',
            ],
        ],
        /*
         * Configuration for the purchase confirmation page.
         *
         * @since [*next-version*]
         */
        'confirmation_page' => [
            /*
             * The datetime format to use when rendering booking dates and times in the confirmation page.
             *
             * @since [*next-version*]
             */
            'booking_datetime_format' => 'l jS M Y, H:i',

            /*
             * Templates used in the confirmation page.
             *
             * @since [*next-version*]
             */
            'templates' => [
                /*
                 * The template for the bookings table in the confirmation page.
                 *
                 * @since [*next-version*]
                 */
                'table' => 'confirmation-table.html',

                /*
                 * The template for the bookings table rows in the confirmation page.
                 *
                 * @since [*next-version*]
                 */
                'booking_row' => 'confirmation-booking-row.html',
            ],
        ],

        /*
         * Optional default timezone to fallback to, for bookings without a timezone.
         * If not given, the fallback timezone is deduced from WordPress or the server.
         *
         * @since [*next-version*]
         */
        'fallback_timezone' => '',

        /*
         * Configuration for placeholder templates.
         */
        'templates' => [
            'token_start'   => '${',
            'token_end'     => '}',
            'token_default' => '',
        ]
    ],
];
