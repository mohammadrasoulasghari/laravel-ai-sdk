<?php

declare(strict_types=1);

namespace App\Modules\Users\Providers;

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Actions\CreateUser;
use App\Modules\Users\Contracts\UserRegistrar;
use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Models\User;
use App\Modules\Users\Policies\UserPolicy;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use App\Shared\Providers\ModuleServiceProvider;

final class UsersServiceProvider extends ModuleServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserRegistrar::class => CreateUser::class,
    ];

    /** @var array<class-string, class-string> */
    protected array $policies = [
        User::class => UserPolicy::class,
    ];

    protected function bootModule(): void
    {
        $this->app->make(PermissionRegistry::class)->register(UserPermission::cases(), [Role::Admin->value]);

        $navigation = $this->app->make(Navigation::class);
        $navigation->add(new NavigationItem(NavigationArea::App, 'users::users.nav.dashboard', 'dashboard', order: 10));
        $navigation->add(new NavigationItem(NavigationArea::Admin, 'users::users.nav.users', 'admin.users.index', UserPermission::Manage->value, 'admin.users.*', order: 20));
    }
}
