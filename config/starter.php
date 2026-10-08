<?php

declare(strict_types=1);

/*
| Starter-kit specific settings. Only read through config('starter.*').
*/
return [
    // Seeded super-admin (DatabaseSeeder). Change before seeding a shared environment.
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    // Default page size and upper bound for ?per_page= on API list endpoints.
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];
