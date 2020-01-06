<?php

return [
    'eddbk' => [
        'session_generator' => [
            'session_type_factories' => [
                'fixed_duration' => 'eddbk_fixed_duration_session_type_factory',
            ],
        ],
    ],
];
