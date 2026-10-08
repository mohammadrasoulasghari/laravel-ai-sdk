<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role as RoleName;
use App\Modules\Roles\Enums\RolePermission;
use App\Modules\Settings\Enums\SettingPermission;
use App\Modules\Users\Enums\UserPermission;
use App\Shared\Auth\SystemAbility;
use App\Shared\Http\Flash;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

function role(RoleName $name): Role
{
    return Role::findByName($name->value, 'web');
}

describe('index', function (): void {
    it('lists roles with user counts and permissions', function (): void {
        $admin = userWithRole(RoleName::Admin->value);
        userWithRole(RoleName::Member->value);
        userWithRole(RoleName::Member->value);

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee(__('roles::roles.title'))
            ->assertSee(__('roles::roles.names.super-admin'))
            ->assertSee(__('roles::roles.names.admin'))
            ->assertSee(__('roles::roles.names.member'))
            ->assertSee(__('roles::roles.all_permissions'))
            ->assertSee(UserPermission::Manage->value)
            ->assertViewHas('roles', fn (Collection $roles): bool => $roles->firstWhere('name', RoleName::Member->value)?->users_count === 2);
    });

    it('offers edit links for every role except super-admin', function (): void {
        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->get(route('admin.roles.index'))
            ->assertSee('href="'.route('admin.roles.edit', role(RoleName::Admin)).'"', false)
            ->assertSee('href="'.route('admin.roles.edit', role(RoleName::Member)).'"', false)
            ->assertDontSee('href="'.route('admin.roles.edit', role(RoleName::SuperAdmin)).'"', false);
    });

    it('forbids users without roles.manage', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, UserPermission::Manage->value))
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('forbids members', function (): void {
        $this->actingAs(userWithRole(RoleName::Member->value))
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('redirects guests', function (): void {
        $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
    });
});

describe('edit', function (): void {
    it('shows every permission with the current ones checked', function (): void {
        $member = role(RoleName::Member);
        $member->givePermissionTo(SettingPermission::Manage->value);

        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->get(route('admin.roles.edit', $member))
            ->assertOk()
            ->assertSee(__('roles::roles.edit_title', ['role' => __('roles::roles.names.member')]))
            ->assertSee(SystemAbility::AccessAdmin->value)
            ->assertSee(RolePermission::Manage->value)
            ->assertSee(UserPermission::Manage->value)
            ->assertSee('value="'.SettingPermission::Manage->value.'"', false)
            ->assertViewHas('permissions', fn (array $permissions): bool => in_array(SettingPermission::Manage->value, $permissions, true));
    });

    it('returns 404 for an unknown role', function (): void {
        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->get(route('admin.roles.edit', ['role' => 999_999]))
            ->assertNotFound();
    });

    it('forbids users without roles.manage', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
            ->get(route('admin.roles.edit', role(RoleName::Member)))
            ->assertForbidden();
    });
});

describe('update', function (): void {
    it('syncs the role permissions', function (): void {
        $member = role(RoleName::Member);

        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->put(route('admin.roles.update', $member), [
                'permissions' => [UserPermission::Manage->value, SettingPermission::Manage->value],
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('roles::roles.updated')]);

        expect($member->refresh()->permissions->pluck('name')->sort()->values()->all())
            ->toBe([SettingPermission::Manage->value, UserPermission::Manage->value]);
    });

    it('grants the new permissions to the role members immediately', function (): void {
        $memberUser = userWithRole(RoleName::Member->value);

        expect($memberUser->can(SettingPermission::Manage->value))->toBeFalse();

        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->put(route('admin.roles.update', role(RoleName::Member)), [
                'permissions' => [SystemAbility::AccessAdmin->value, SettingPermission::Manage->value],
            ]);

        $this->actingAs($memberUser->refresh())->get(route('admin.settings.edit'))->assertOk();
    });

    it('removes all permissions when none are submitted', function (): void {
        $admin = role(RoleName::Admin);
        expect($admin->permissions)->not->toBeEmpty();

        $this->actingAs(userWithRole(RoleName::SuperAdmin->value))
            ->put(route('admin.roles.update', $admin), [])
            ->assertRedirect(route('admin.roles.index'));

        expect($admin->refresh()->permissions)->toBeEmpty();
    });

    it('validates permission names', function (mixed $permissions, string $field): void {
        $member = role(RoleName::Member);

        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->from(route('admin.roles.edit', $member))
            ->put(route('admin.roles.update', $member), ['permissions' => $permissions])
            ->assertRedirect(route('admin.roles.edit', $member))
            ->assertSessionHasErrors($field);

        expect($member->refresh()->permissions)->toBeEmpty();
    })->with([
        'unknown permission' => [['do.anything'], 'permissions.0'],
        'not an array' => ['users.manage', 'permissions'],
    ]);

    it('refuses to change the super-admin role', function (): void {
        $super = role(RoleName::SuperAdmin);

        $this->actingAs(userWithRole(RoleName::Admin->value))
            ->from(route('admin.roles.index'))
            ->put(route('admin.roles.update', $super), ['permissions' => [UserPermission::Manage->value]])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'error', 'message' => __('roles::roles.errors.super_admin_immutable')]);

        expect($super->refresh()->permissions)->toBeEmpty();
    });

    it('refuses to change the super-admin role even for super admins', function (): void {
        $this->actingAs(userWithRole(RoleName::SuperAdmin->value))
            ->put(route('admin.roles.update', role(RoleName::SuperAdmin)), ['permissions' => [UserPermission::Manage->value]])
            ->assertSessionHas('flash.type', 'error');

        expect(role(RoleName::SuperAdmin)->permissions)->toBeEmpty();
    });

    it('forbids users without roles.manage', function (): void {
        $member = role(RoleName::Member);

        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, UserPermission::Manage->value))
            ->put(route('admin.roles.update', $member), ['permissions' => [RolePermission::Manage->value]])
            ->assertForbidden();

        expect($member->refresh()->permissions)->toBeEmpty();
    });

    it('lets members not escalate their own role', function (): void {
        $member = role(RoleName::Member);

        $this->actingAs(userWithRole(RoleName::Member->value))
            ->put(route('admin.roles.update', $member), ['permissions' => [SystemAbility::AccessAdmin->value]])
            ->assertForbidden();

        expect($member->refresh()->permissions)->toBeEmpty();
    });
});

it('lets super admins pass every gate without holding permissions', function (): void {
    $super = userWithRole(RoleName::SuperAdmin->value);

    expect($super->getAllPermissions())->toBeEmpty()
        ->and($super->can(RolePermission::Manage->value))->toBeTrue()
        ->and($super->can('an-ability-nobody-defined'))->toBeTrue();

    $this->actingAs($super)->get(route('admin.roles.index'))->assertOk();
    $this->get(route('admin.roles.edit', role(RoleName::Member)))->assertOk();
    $this->put(route('admin.roles.update', role(RoleName::Member)), ['permissions' => [UserPermission::Manage->value]])
        ->assertRedirect(route('admin.roles.index'));

    expect(role(RoleName::Member)->hasPermissionTo(UserPermission::Manage->value))->toBeTrue();
});

it('does not let admins pass undefined abilities', function (): void {
    expect(userWithRole(RoleName::Admin->value)->can('an-ability-nobody-defined'))->toBeFalse();
});
