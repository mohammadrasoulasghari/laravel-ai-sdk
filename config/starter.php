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

    // Requests per minute for named rate limiters (defined in each module provider's bootModule()).
    'rate_limits' => [
        'api' => 60,
        'auth' => 10,
    ],

    // Default page size and upper bound for ?per_page= on API list endpoints.
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
];
