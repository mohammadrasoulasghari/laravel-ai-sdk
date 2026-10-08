<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Shared\Providers\SharedServiceProvider;

/*
| Application and module service providers. Module providers are appended by
| `php artisan make:module` — keep one per line, alphabetical after the app ones.
*/
return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    SharedServiceProvider::class,
];
