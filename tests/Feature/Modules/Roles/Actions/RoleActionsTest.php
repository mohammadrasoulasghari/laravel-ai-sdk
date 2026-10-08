<?php

declare(strict_types=1);

use App\Modules\Roles\Actions\UpdateRolePermissions;
use App\Modules\Roles\Contracts\RoleCatalog;
use App\Modules\Roles\Data\RolePermissionsData;
use App\Modules\Roles\Enums\Role as RoleName;
use App\Modules\Roles\Exceptions\SuperAdminRoleIsImmutableException;
use App\Modules\Roles\Queries\PermissionNamesQuery;
use App\Modules\Roles\Queries\RoleOptionsQuery;
use App\Modules\Roles\Queries\RolesIndexQuery;
use App\Modules\Users\Enums\UserPermission;
use Spatie\Permission\Models\Role;

it('syncs permissions on a regular role', function (): void {
    $member = Role::findByName(RoleName::Member->value);

    $result = resolve(UpdateRolePermissions::class)->handle($member, new RolePermissionsData([UserPermission::Manage->value]));

    expect($result->permissions->pluck('name')->all())->toBe([UserPermission::Manage->value]);
});

it('throws for the super-admin role', function (): void {
    $super = Role::findByName(RoleName::SuperAdmin->value);

    expect(fn (): Role => resolve(UpdateRolePermissions::class)->handle($super, new RolePermissionsData([UserPermission::Manage->value])))
        ->toThrow(SuperAdminRoleIsImmutableException::class);

    expect($super->refresh()->permissions)->toBeEmpty();
});

it('binds the role catalog to translated role options', function (): void {
    $catalog = resolve(RoleCatalog::class);

    expect($catalog)->toBeInstanceOf(RoleOptionsQuery::class)
        ->and($catalog->options())->toBe([
            'admin' => __('roles::roles.names.admin'),
            'member' => __('roles::roles.names.member'),
            'super-admin' => __('roles::roles.names.super-admin'),
        ]);
});

it('falls back to the raw name for untranslated roles', function (): void {
    Role::findOrCreate('auditor', 'web');

    expect(resolve(RoleCatalog::class)->options())->toHaveKey('auditor', 'auditor');
});

it('translates role options to Persian', function (): void {
    app()->setLocale('fa');

    expect(resolve(RoleCatalog::class)->options()['admin'])->toBe(__('roles::roles.names.admin', locale: 'fa'));
});

it('lists permission names alphabetically', function (): void {
    $names = resolve(PermissionNamesQuery::class)->handle();

    expect($names)->toBe(collect($names)->sort()->values()->all())
        ->and($names)->toContain(UserPermission::Manage->value);
});

it('loads roles with permissions and user counts', function (): void {
    $roles = resolve(RolesIndexQuery::class)->handle();

    expect($roles->pluck('name')->all())->toBe(['admin', 'member', 'super-admin'])
        ->and($roles->first()?->relationLoaded('permissions'))->toBeTrue()
        ->and($roles->first()?->users_count)->toBe(0);
});
