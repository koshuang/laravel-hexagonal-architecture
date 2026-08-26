<?php

return [
    'lint' => [
        'env_outside_config' => true,
        'missing_example_keys' => true,
        'duplicate_env_keys' => true,
    ],

    'application_config' => [
        'config/account.php',
    ],

    'env_files' => [
        '.env.example',
        '.env.testing',
    ],

    'required' => [
        'local' => [
            'account.maximum_transfer_threshold',
        ],
        'production' => [
            'account.maximum_transfer_threshold',
        ],
    ],
];
