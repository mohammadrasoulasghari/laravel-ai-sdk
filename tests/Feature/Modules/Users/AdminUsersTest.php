<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Models\User;
use App\Shared\Auth\SystemAbility;
use App\Shared\Http\Flash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Sanctum\PersonalAccessToken;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

describe('index', function (): void {
    it('redirects guests to login', function (): void {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    });

    it('forbids members', function (): void {
        $this->actingAs(userWithRole(Role::Member->value))
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('forbids admin panel users without users.manage', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('forbids users.manage without admin panel access', function (): void {
        $this->actingAs(userWithPermissions(UserPermission::Manage->value))
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('lists users for admins', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $member = userWithRole(Role::Member->value);
        $unverified = User::factory()->unverified()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(__('users::users.admin.title'))
            ->assertSee($admin->email)
            ->assertSee($member->email)
            ->assertSee($unverified->email)
            ->assertSee(__('roles::roles.names.member'))
            ->assertSee(__('users::users.admin.unverified'))
            ->assertSee(route('admin.users.edit', $member), false);
    });

    it('lets users with exactly the needed permissions in', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, UserPermission::Manage->value))
            ->get(route('admin.users.index'))
            ->assertOk();
    });

    it('searches by name and email', function (): void {
        $admin = userWithRole(Role::Admin->value);
        User::factory()->create(['name' => 'Zelda Hyrule', 'email' => 'zelda@example.com']);
        User::factory()->create(['name' => 'Link Hero', 'email' => 'hero@castle.test']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Zelda']))
            ->assertOk()
            ->assertSee('zelda@example.com')
            ->assertDontSee('hero@castle.test')
            ->assertSee('value="Zelda"', false);

        $this->get(route('admin.users.index', ['search' => 'castle.test']))
            ->assertSee('hero@castle.test')
            ->assertDontSee('zelda@example.com');

        $this->get(route('admin.users.index', ['search' => 'no-such-user']))
            ->assertOk()
            ->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->total() === 0);
    });

    it('ignores a blank search', function (): void {
        $admin = userWithRole(Role::Admin->value);
        User::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => '   ']))
            ->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->total() === 3);
    });

    it('paginates 15 users per page and keeps the query string', function (): void {
        $admin = userWithRole(Role::Admin->value);
        User::factory()->count(20)->create(['name' => 'Paged Person']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->count() === 15 && $users->total() === 21 && $users->lastPage() === 2);

        $this->get(route('admin.users.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->count() === 6 && $users->currentPage() === 2);

        $this->get(route('admin.users.index', ['search' => 'Paged']))
            ->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->total() === 20 && str_contains((string) $users->nextPageUrl(), 'search=Paged'));
    });
});

describe('edit and update', function (): void {
    it('shows the edit form', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $member = userWithRole(Role::Member->value);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $member))
            ->assertOk()
            ->assertSee(__('users::users.admin.edit_title', ['name' => $member->name]))
            ->assertSee($member->email)
            ->assertSee(__('roles::roles.names.super-admin'))
            ->assertSee(__('roles::roles.names.admin'))
            ->assertSee('value="member"', false);
    });

    it('forbids the edit form without users.manage', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
            ->get(route('admin.users.edit', User::factory()->create()))
            ->assertForbidden();
    });

    it('returns 404 for an unknown user', function (): void {
        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.users.edit', ['user' => '01HZZZZZZZZZZZZZZZZZZZZZZZ']))
            ->assertNotFound();
    });

    it('updates name and syncs roles', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $member = userWithRole(Role::Member->value);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $member), [
                'name' => 'Renamed',
                'email' => $member->email,
                'roles' => [Role::Admin->value],
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('users::users.updated')]);

        $member->refresh();

        expect($member->name)->toBe('Renamed')
            ->and($member->getRoleNames()->all())->toBe([Role::Admin->value])
            ->and($member->email_verified_at)->not->toBeNull();
    });

    it('removes every role when none are submitted', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.users.update', $member), ['name' => $member->name, 'email' => $member->email])
            ->assertRedirect(route('admin.users.index'));

        expect($member->refresh()->getRoleNames()->all())->toBe([]);
    });

    it('resets verification when the email changes', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.users.update', $member), [
                'name' => $member->name,
                'email' => 'new-address@example.com',
                'roles' => [Role::Member->value],
            ])
            ->assertRedirect(route('admin.users.index'));

        $member->refresh();

        expect($member->email)->toBe('new-address@example.com')
            ->and($member->email_verified_at)->toBeNull()
            ->and($member->hasVerifiedEmail())->toBeFalse();
    });

    it('validates the update', function (array $payload, string $field): void {
        User::factory()->create(['email' => 'taken@example.com']);
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.users.edit', $member))
            ->put(route('admin.users.update', $member), [
                'name' => 'Valid Name',
                'email' => $member->email,
                'roles' => [Role::Member->value],
                ...$payload,
            ])
            ->assertRedirect(route('admin.users.edit', $member))
            ->assertSessionHasErrors($field);

        expect($member->refresh()->name)->not->toBe('Valid Name');
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'long name' => [['name' => str_repeat('a', 256)], 'name'],
        'missing email' => [['email' => ''], 'email'],
        'invalid email' => [['email' => 'nope'], 'email'],
        'uppercase email' => [['email' => 'Upper@Example.com'], 'email'],
        'duplicate email' => [['email' => 'taken@example.com'], 'email'],
        'roles not an array' => [['roles' => 'admin'], 'roles'],
        'unknown role' => [['roles' => ['wizard']], 'roles.0'],
    ]);

    it('allows keeping the same email', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.users.update', $member), ['name' => 'Same Email', 'email' => $member->email, 'roles' => []])
            ->assertSessionHasNoErrors();

        expect($member->refresh()->name)->toBe('Same Email');
    });

    it('forbids updates without users.manage', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
            ->put(route('admin.users.update', $member), ['name' => 'Hacked', 'email' => $member->email, 'roles' => [Role::SuperAdmin->value]])
            ->assertForbidden();

        expect($member->refresh()->name)->not->toBe('Hacked')
            ->and($member->hasRole(Role::SuperAdmin->value))->toBeFalse();
    });

    it('forbids members from promoting themselves', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs($member)
            ->put(route('admin.users.update', $member), ['name' => $member->name, 'email' => $member->email, 'roles' => [Role::SuperAdmin->value]])
            ->assertForbidden();

        expect($member->refresh()->hasRole(Role::SuperAdmin->value))->toBeFalse();
    });
});

describe('destroy', function (): void {
    it('refuses to delete yourself', function (): void {
        $admin = userWithRole(Role::Admin->value);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'error', 'message' => __('users::users.errors.cannot_delete_self')]);

        $this->assertModelExists($admin);
    });

    it('deletes another user and revokes their tokens', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $member = userWithRole(Role::Member->value);
        $member->createToken('phone');
        $member->createToken('laptop');

        $admin->createToken('admin-cli');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $member))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('users::users.deleted')]);

        $this->assertModelMissing($member);

        expect(PersonalAccessToken::query()->where('tokenable_id', $member->id)->count())->toBe(0)
            ->and(PersonalAccessToken::query()->where('tokenable_id', $admin->id)->count())->toBe(1);
    });

    it('forbids deleting without users.manage', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value))
            ->delete(route('admin.users.destroy', $member))
            ->assertForbidden();

        $this->assertModelExists($member);
    });

    it('redirects guests trying to delete', function (): void {
        $member = userWithRole(Role::Member->value);

        $this->delete(route('admin.users.destroy', $member))->assertRedirect(route('login'));

        $this->assertModelExists($member);
    });
});

it('lets super admins manage users without explicit permissions', function (): void {
    $super = userWithRole(Role::SuperAdmin->value);
    $member = userWithRole(Role::Member->value);

    expect($super->getAllPermissions())->toBeEmpty();

    $this->actingAs($super)->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.users.edit', $member))->assertOk();
    $this->delete(route('admin.users.destroy', $member))->assertRedirect(route('admin.users.index'));

    $this->assertModelMissing($member);
});
