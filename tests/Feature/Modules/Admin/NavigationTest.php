<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Settings\Enums\SettingPermission;
use App\Shared\Auth\SystemAbility;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

it('shows the admin link in the main navigation to admins', function (string $role): void {
    $this->actingAs(userWithRole($role))
        ->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('admin.dashboard').'"', false)
        ->assertSee(__('admin::admin.nav'));
})->with([Role::Admin->value, Role::SuperAdmin->value]);

it('shows the admin link to anyone holding admin.access', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
        ->get(route('home'))
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});

it('hides the admin link from members', function (): void {
    $this->actingAs(userWithRole(Role::Member->value))
        ->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertDontSee('href="'.route('admin.dashboard').'"', false);
});

it('hides the admin link from guests', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('href="'.route('admin.dashboard').'"', false);
});

it('hides the admin link from users with module permissions but no admin.access', function (): void {
    $this->actingAs(userWithPermissions(SettingPermission::Manage->value))
        ->get(route('home'))
        ->assertDontSee('href="'.route('admin.dashboard').'"', false);
});

it('filters the admin sidebar by permission', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, SettingPermission::Manage->value))
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('href="'.route('admin.settings.edit').'"', false)
        ->assertSee('href="'.route('admin.dashboard').'"', false)
        ->assertDontSee('href="'.route('admin.users.index').'"', false)
        ->assertDontSee('href="'.route('admin.roles.index').'"', false);
});

it('marks the current admin section as active', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSeeInOrder(['href="'.route('admin.roles.index').'"', 'aria-current="page"'], false);
});
