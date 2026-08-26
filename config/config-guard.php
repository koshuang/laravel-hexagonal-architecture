<?php

return [
    'lint' => [
        'env_outside_config' => true,
        'missing_example_keys' => true,
        'duplicate_env_keys' => true,
    ],

    'application_config' => [
        'config/transfer.php',
    ],

    'env_files' => [
        '.env.example',
        '.env.testing',
    ],

    'required' => [
        'local' => [
            'transfer.maximum_transfer_threshold',
        ],
        'production' => [
            'transfer.maximum_transfer_threshold',
        ],
    ],
];
