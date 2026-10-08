<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;

use function Tests\userWithRole;

/*
 * KNOWN APP BUG: users::dashboard reads $currentUser, which LayoutComposer only shares
 * with components.layouts.* views, so GET /dashboard throws "Undefined variable
 * $currentUser" (500) for every verified user. Remove the skip once fixed.
 */

it('greets verified users on the dashboard', function (): void {
    $user = userWithRole(Role::Member->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('users::users.dashboard.greeting', ['name' => $user->name]), false)
        ->assertSee(__('users::users.nav.dashboard'));
});

it('hides the dashboard admin button from members', function (): void {
    $this->actingAs(userWithRole(Role::Member->value))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(__('users::users.dashboard.admin_link'))
        ->assertDontSee('href="'.route('admin.dashboard').'"', false);
});

it('shows the dashboard admin button to admins and super admins', function (string $role): void {
    $this->actingAs(userWithRole($role))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('users::users.dashboard.admin_link'))
        ->assertSee('href="'.route('admin.dashboard').'"', false);
})->with([Role::Admin->value, Role::SuperAdmin->value]);
