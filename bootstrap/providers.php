<?php

declare(strict_types=1);

use App\Modules\Admin\Providers\AdminServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Roles\Providers\RolesServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Modules\Users\Providers\UsersServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Shared\Providers\SharedServiceProvider;

/*
| Application and module service providers. `php artisan make:module` appends
| module providers below the "modules" marker — keep one per line.
*/
return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    SharedServiceProvider::class,

    // modules
    AdminServiceProvider::class,
    AuthServiceProvider::class,
    RolesServiceProvider::class,
    SettingsServiceProvider::class,
    UsersServiceProvider::class,
];
