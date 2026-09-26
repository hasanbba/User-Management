<?php

return [
    'admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Local Administrator'),
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.test'),
        'password' => env('SEED_ADMIN_PASSWORD'),
        'role' => 'admin',
    ],
    'user' => [
        'name' => env('SEED_USER_NAME', 'Local User'),
        'email' => env('SEED_USER_EMAIL', 'user@example.test'),
        'password' => env('SEED_USER_PASSWORD'),
        'role' => 'user',
    ],
];
