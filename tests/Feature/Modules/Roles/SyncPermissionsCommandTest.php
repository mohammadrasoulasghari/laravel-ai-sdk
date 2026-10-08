<?php

declare(strict_types=1);

use App\Modules\Roles\Actions\SyncPermissions;
use App\Modules\Roles\Enums\Role as RoleName;
use App\Modules\Roles\Enums\RolePermission;
use App\Modules\Settings\Enums\SettingPermission;
use App\Modules\Users\Enums\UserPermission;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Auth\SystemAbility;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** @return array{roles: int, permissions: int, grants: int} */
function permissionCounts(): array
{
    return [
        'roles' => Role::query()->count(),
        'permissions' => Permission::query()->count(),
        'grants' => DB::table('role_has_permissions')->count(),
    ];
}

it('is idempotent', function (): void {
    $this->artisan('permissions:sync')
        ->expectsOutputToContain('Synced '.count(resolve(PermissionRegistry::class)->all()).' permissions.')
        ->assertSuccessful();

    $first = permissionCounts();

    $this->artisan('permissions:sync')->assertSuccessful();

    expect(permissionCounts())->toBe($first)
        ->and($first['roles'])->toBe(count(RoleName::cases()))
        ->and($first['permissions'])->toBe(count(resolve(PermissionRegistry::class)->all()));
});

it('creates every built-in role and registered permission', function (): void {
    expect(Role::query()->orderBy('name')->pluck('name')->all())->toBe(['admin', 'member', 'super-admin'])
        ->and(Permission::query()->orderBy('name')->pluck('name')->all())->toBe([
            SystemAbility::AccessAdmin->value,
            RolePermission::Manage->value,
            SettingPermission::Manage->value,
            SystemAbility::ViewSystemTools->value,
            UserPermission::Manage->value,
        ]);
});

it('grants default permissions to admins only', function (): void {
    $admin = Role::findByName(RoleName::Admin->value);

    expect($admin->permissions->pluck('name')->sort()->values()->all())->toBe(Permission::query()->orderBy('name')->pluck('name')->all())
        ->and(Role::findByName(RoleName::Member->value)->permissions)->toBeEmpty()
        ->and(Role::findByName(RoleName::SuperAdmin->value)->permissions)->toBeEmpty();
});

it('never revokes grants added manually', function (): void {
    Role::findByName(RoleName::Member->value)->givePermissionTo(UserPermission::Manage->value);

    $this->artisan('permissions:sync')->assertSuccessful();

    expect(Role::findByName(RoleName::Member->value)->hasPermissionTo(UserPermission::Manage->value))->toBeTrue();
});

it('restores deleted defaults', function (): void {
    Role::findByName(RoleName::Admin->value)->revokePermissionTo(UserPermission::Manage->value);
    Permission::findByName(SettingPermission::Manage->value)->delete();

    $count = resolve(SyncPermissions::class)->handle();

    $admin = Role::findByName(RoleName::Admin->value);

    expect($count)->toBe(5)
        ->and($admin->hasPermissionTo(UserPermission::Manage->value))->toBeTrue()
        ->and($admin->hasPermissionTo(SettingPermission::Manage->value))->toBeTrue();
});
