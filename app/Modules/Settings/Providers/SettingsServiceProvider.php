<?php

declare(strict_types=1);

namespace App\Modules\Settings\Providers;

use App\Modules\Roles\Enums\Role;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingPermission;
use App\Modules\Settings\Queries\SettingsQuery;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use App\Shared\Providers\ModuleServiceProvider;

final class SettingsServiceProvider extends ModuleServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        SettingsStore::class => SettingsQuery::class,
    ];

    protected function bootModule(): void
    {
        $this->app->make(PermissionRegistry::class)->register(SettingPermission::cases(), [Role::Admin->value]);

        $this->app->make(Navigation::class)->add(
            new NavigationItem(NavigationArea::Admin, 'settings::settings.nav', 'admin.settings.edit', SettingPermission::Manage->value, 'admin.settings.*', order: 90),
        );
    }
}
