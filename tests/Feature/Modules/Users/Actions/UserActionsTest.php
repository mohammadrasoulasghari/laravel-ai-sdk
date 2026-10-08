<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Actions\CreateUser;
use App\Modules\Users\Actions\DeleteUser;
use App\Modules\Users\Actions\UpdateUser;
use App\Modules\Users\Contracts\UserRegistrar;
use App\Modules\Users\Data\NewUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Exceptions\CannotDeleteSelfException;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

use function Tests\userWithRole;

it('binds the registrar contract to CreateUser', function (): void {
    expect(resolve(UserRegistrar::class))->toBeInstanceOf(CreateUser::class);
});

it('creates a member with a lower-cased email and hashed password', function (): void {
    $user = resolve(UserRegistrar::class)->register(new NewUserData('Jane', 'Jane@Example.COM', 'secret-password'));

    expect($user->email)->toBe('jane@example.com')
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->hasRole(Role::Member->value))->toBeTrue()
        ->and($user->email_verified_at)->toBeNull();
});

it('updates a user and syncs roles inside a transaction', function (): void {
    $user = userWithRole(Role::Member->value);

    $updated = resolve(UpdateUser::class)->handle($user, new UpdateUserData('New Name', $user->email, [Role::Admin->value, Role::Member->value]));

    expect($updated->name)->toBe('New Name')
        ->and($updated->getRoleNames()->sort()->values()->all())->toBe([Role::Admin->value, Role::Member->value])
        ->and($updated->email_verified_at)->not->toBeNull();
});

it('lower-cases a changed email and clears verification', function (): void {
    $user = userWithRole(Role::Member->value);

    resolve(UpdateUser::class)->handle($user, new UpdateUserData($user->name, 'NEW@Example.com', []));

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

it('keeps verification when only the email case differs', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    resolve(UpdateUser::class)->handle($user, new UpdateUserData($user->name, 'JANE@example.com', []));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

it('rolls back the profile change when the role sync fails', function (): void {
    $user = userWithRole(Role::Member->value);
    $originalName = $user->name;

    expect(fn (): User => resolve(UpdateUser::class)->handle($user, new UpdateUserData('Changed', $user->email, ['no-such-role'])))
        ->toThrow(RoleDoesNotExist::class);

    expect(User::query()->findOrFail($user->id)->name)->toBe($originalName);
});

it('refuses to delete the acting user', function (): void {
    $user = userWithRole(Role::Admin->value);

    expect(fn (): null => resolve(DeleteUser::class)->handle($user, $user))
        ->toThrow(CannotDeleteSelfException::class);

    $this->assertModelExists($user);
});

it('deletes another user together with their tokens', function (): void {
    $actor = userWithRole(Role::Admin->value);
    $user = userWithRole(Role::Member->value);
    $user->createToken('phone');

    resolve(DeleteUser::class)->handle($user, $actor);

    $this->assertModelMissing($user);
    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
});
