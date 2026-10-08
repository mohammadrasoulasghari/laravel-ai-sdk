<?php

declare(strict_types=1);

use App\Modules\Roles\Data\RolePermissionsData;
use App\Modules\Roles\Enums\Role;
use App\Modules\Roles\Enums\RolePermission;
use App\Modules\Roles\Exceptions\SuperAdminRoleIsImmutableException;
use App\Modules\Roles\Http\Requests\UpdateRolePermissionsRequest;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Auth\SystemAbility;

it('defines the built-in roles', function (): void {
    expect(array_map(fn (Role $role): string => $role->value, Role::cases()))->toBe(['super-admin', 'admin', 'member']);
});

it('translates every built-in role name', function (Role $role, string $locale): void {
    $key = 'roles::roles.names.'.$role->value;

    expect(__($key, locale: $locale))->toBeString()->not->toBe($key);
})->with(Role::cases())->with(['en', 'fa']);

it('describes the super-admin immutable exception', function (): void {
    $exception = new SuperAdminRoleIsImmutableException('x');

    expect($exception->errorCode())->toBe('roles.super_admin_immutable')
        ->and($exception->status())->toBe(422)
        ->and(__($exception->translationKey()))->toBe(__('roles::roles.errors.super_admin_immutable'))
        ->and(__($exception->translationKey()))->not->toBe($exception->translationKey());
});

it('registers module permissions with admin as the default role', function (): void {
    expect(resolve(PermissionRegistry::class)->all())->toMatchArray([
        SystemAbility::AccessAdmin->value => ['admin'],
        RolePermission::Manage->value => ['admin'],
        'settings.manage' => ['admin'],
        SystemAbility::ViewSystemTools->value => ['admin'],
        'users.manage' => ['admin'],
    ]);
});

it('merges default roles when a permission is registered twice', function (): void {
    $registry = new PermissionRegistry;
    $registry->register([RolePermission::Manage], ['admin']);
    $registry->register([RolePermission::Manage], ['admin', 'member']);

    expect($registry->all())->toBe([RolePermission::Manage->value => ['admin', 'member']]);
});

it('maps the update request to data', function (): void {
    $data = UpdateRolePermissionsRequest::create('/admin/roles/1', 'PUT', ['permissions' => ['x' => 'users.manage']])->toData();

    expect($data)->toBeInstanceOf(RolePermissionsData::class)
        ->and($data->permissions)->toBe(['users.manage']);
});
