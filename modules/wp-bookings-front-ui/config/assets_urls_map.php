<?php

return [
    'wizard_ui/dist/app.min.js' => plugins_url(
        RC_BOOKINGS_FRONT_UI_MODULE_RELATIVE_DIR . '/dist/' . RC_BOOKINGS_FRONT_UI_APP_VERSION . '/app.min.js',
        EDDBK_FILE
    ),
    'wizard_ui/dist/app.min.css' => plugins_url(
        RC_BOOKINGS_FRONT_UI_MODULE_RELATIVE_DIR . '/dist/' . RC_BOOKINGS_FRONT_UI_APP_VERSION . '/app.min.css',
        EDDBK_FILE
    ),
];
