<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Models\User;
use App\Shared\Auth\SystemAbility;
use App\Shared\Navigation\NavigationItem;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

it('redirects guests to login', function (): void {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids members', function (): void {
    $this->actingAs(userWithRole(Role::Member->value))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('forbids users without any role', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('sends unverified admins to the verification notice', function (): void {
    $admin = User::factory()->unverified()->create();
    $admin->assignRole(Role::Admin->value);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('shows every section to admins', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(__('admin::admin.dashboard'))
        ->assertSee(__('admin::admin.intro'))
        ->assertSee(__('users::users.nav.users'))
        ->assertSee(__('roles::roles.nav'))
        ->assertSee(__('settings::settings.nav'))
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.roles.index').'"', false)
        ->assertSee('href="'.route('admin.settings.edit').'"', false)
        ->assertViewHas('sections', fn (array $sections): bool => array_map(fn (NavigationItem $item): string => $item->route, $sections) === [
            'admin.users.index',
            'admin.roles.index',
            'admin.settings.edit',
        ]);
});

it('lets super admins in without explicit permissions', function (): void {
    $this->actingAs(userWithRole(Role::SuperAdmin->value))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('sections', fn (array $sections): bool => count($sections) === 3);
});

it('only lists sections the user may open', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, UserPermission::Manage->value))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('sections', fn (array $sections): bool => array_map(fn (NavigationItem $item): string => $item->route, $sections) === ['admin.users.index']);
});

it('shows no sections to a user with only admin.access', function (): void {
    $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('sections', [])
        ->assertDontSee('href="'.route('admin.users.index').'"', false);
});

it('renders the admin dashboard in Persian', function (): void {
    $this->actingAs(userWithRole(Role::Admin->value))
        ->withSession(['locale' => 'fa'])
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee(__('admin::admin.intro', locale: 'fa'));
});
