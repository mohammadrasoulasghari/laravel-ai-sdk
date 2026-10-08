<?php

declare(strict_types=1);

namespace App\Modules\Roles\Providers;

use App\Modules\Roles\Console\SyncPermissionsCommand;
use App\Modules\Roles\Contracts\RoleCatalog;
use App\Modules\Roles\Enums\Role as RoleName;
use App\Modules\Roles\Enums\RolePermission;
use App\Modules\Roles\Policies\RolePolicy;
use App\Modules\Roles\Queries\RoleOptionsQuery;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use App\Shared\Providers\ModuleServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

final class RolesServiceProvider extends ModuleServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        RoleCatalog::class => RoleOptionsQuery::class,
    ];

    /** @var array<class-string, class-string> */
    protected array $policies = [
        Role::class => RolePolicy::class,
    ];

    protected function bootModule(): void
    {
        // Super admins can do everything; returning null defers to normal checks.
        Gate::before(static fn (Authenticatable $user): ?bool => method_exists($user, 'hasRole') && $user->hasRole(RoleName::SuperAdmin->value) ? true : null);

        Route::bind('role', static fn (string $value): Role => Role::query()->findOrFail($value));

        $this->app->make(PermissionRegistry::class)->register(RolePermission::cases(), [RoleName::Admin->value]);

        $this->app->make(Navigation::class)->add(
            new NavigationItem(NavigationArea::Admin, 'roles::roles.nav', 'admin.roles.index', RolePermission::Manage->value, 'admin.roles.*', order: 30),
        );

        if ($this->app->runningInConsole()) {
            $this->commands([SyncPermissionsCommand::class]);
        }
    }
}
