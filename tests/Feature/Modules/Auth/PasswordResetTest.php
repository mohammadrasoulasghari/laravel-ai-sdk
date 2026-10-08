<?php

declare(strict_types=1);

use App\Modules\Users\Models\User;
use App\Shared\Http\Flash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('shows the forgot password page', function (): void {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee(__('auth::auth.forgot_title'));
});

it('emails a reset link to a known user', function (): void {
    Notification::fake();
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'Jane@Example.com'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'info', 'message' => __('auth::auth.reset_link_sent')]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('gives the same response for an unknown email', function (): void {
    Notification::fake();
    User::factory()->create(['email' => 'jane@example.com']);

    $known = $this->from(route('password.request'))->post(route('password.email'), ['email' => 'jane@example.com']);
    $unknown = $this->from(route('password.request'))->post(route('password.email'), ['email' => 'nobody@example.com']);

    $unknown->assertRedirect(route('password.request'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'info', 'message' => __('auth::auth.reset_link_sent')]);

    expect($unknown->getStatusCode())->toBe($known->getStatusCode())
        ->and($unknown->headers->get('Location'))->toBe($known->headers->get('Location'));

    Notification::assertSentTimes(ResetPassword::class, 1);
});

it('validates the forgot password email', function (): void {
    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

it('shows the reset form with token and email', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => 'jane@example.com']))
        ->assertOk()
        ->assertSee(__('auth::auth.reset_title'))
        ->assertSee($token, false)
        ->assertSee('jane@example.com', false);
});

it('resets the password with a valid token', function (): void {
    Event::fake([PasswordReset::class]);
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $user->createToken('phone');

    $token = Password::createToken($user);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('passwords.reset')]);

    $user->refresh();

    expect(Hash::check('brand-new-password', $user->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);

    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->getAuthIdentifier() === $user->id);

    $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'brand-new-password'])
        ->assertRedirect(route('dashboard', absolute: false));
});

it('rejects an invalid token', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    Password::createToken($user);

    $this->from(route('password.reset', ['token' => 'bogus']))
        ->post(route('password.store'), [
            'token' => 'bogus',
            'email' => 'jane@example.com',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])
        ->assertRedirect(route('password.reset', ['token' => 'bogus']))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'error', 'message' => __('passwords.token')])
        ->assertSessionMissing('_old_input.password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

it('rejects a token for a different user', function (): void {
    $jane = User::factory()->create(['email' => 'jane@example.com']);
    User::factory()->create(['email' => 'john@example.com']);
    $token = Password::createToken($jane);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'john@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHas('flash.type', 'error');

    expect(Hash::check('password', User::query()->where('email', 'john@example.com')->sole()->password))->toBeTrue();
});

it('validates the reset form', function (array $payload, string $field): void {
    $this->post(route('password.store'), [
        'token' => 'token',
        'email' => 'jane@example.com',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
        ...$payload,
    ])->assertSessionHasErrors($field);
})->with([
    'missing token' => [['token' => ''], 'token'],
    'invalid email' => [['email' => 'nope'], 'email'],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'unconfirmed password' => [['password_confirmation' => 'different-password'], 'password'],
]);
