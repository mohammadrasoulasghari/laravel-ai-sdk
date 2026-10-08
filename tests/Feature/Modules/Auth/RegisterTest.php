<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Settings\Actions\UpdateSettings;
use App\Modules\Settings\Data\SettingsData;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Users\Models\User;
use App\Shared\Http\Flash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function closeRegistration(): void
{
    resolve(UpdateSettings::class)->handle(new SettingsData([
        SettingKey::RegistrationOpen->value => false,
        SettingKey::SupportEmail->value => 'support@example.com',
    ]));
}

/** @return array<string, string> */
function registrationPayload(array $overrides = []): array
{
    return [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        ...$overrides,
    ];
}

it('shows the registration page', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee(__('auth::auth.register_title'))
        ->assertSee('name="password_confirmation"', false);
});

it('registers a member, logs them in and asks for verification', function (): void {
    Notification::fake();

    $this->post(route('register'), registrationPayload(['name' => '  Jane Doe  ']))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'jane@example.com')->sole();

    expect($user->name)->toBe('Jane Doe')
        ->and($user->email_verified_at)->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->hasRole(Role::Member->value))->toBeTrue()
        ->and($user->getRoleNames()->all())->toBe([Role::Member->value]);

    $this->assertAuthenticatedAs($user);
    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertSentTimes(VerifyEmail::class, 1);
});

it('dispatches the Registered event', function (): void {
    Event::fake([Registered::class]);

    $this->post(route('register'), registrationPayload());

    Event::assertDispatched(Registered::class, fn (Registered $event): bool => $event->user->getEmailForVerification() === 'jane@example.com');
});

it('validates registration input', function (array $overrides, string $field): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->from(route('register'))
        ->post(route('register'), registrationPayload($overrides))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors($field);

    $this->assertGuest();
    expect(User::query()->count())->toBe(1);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'invalid email' => [['email' => 'not-an-email'], 'email'],
    'uppercase email' => [['email' => 'Jane@Example.com'], 'email'],
    'duplicate email' => [['email' => 'taken@example.com'], 'email'],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'unconfirmed password' => [['password_confirmation' => 'something-else'], 'password'],
]);

it('refuses registration when it is closed in settings', function (): void {
    Notification::fake();
    closeRegistration();

    $this->from(route('register'))
        ->post(route('register'), registrationPayload())
        ->assertRedirect(route('register'))
        ->assertSessionHas(Flash::SESSION_KEY, [
            'type' => 'error',
            'message' => __('auth::auth.errors.registration_closed'),
        ])
        ->assertSessionHasInput('email', 'jane@example.com')
        ->assertSessionMissing('_old_input.password');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('answers closed registration with 403 problem+json for JSON clients', function (): void {
    closeRegistration();

    $this->postJson(route('register'), registrationPayload())
        ->assertForbidden()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'urn:problem:auth.registration_closed')
        ->assertJsonPath('detail', __('auth::auth.errors.registration_closed'));

    expect(User::query()->count())->toBe(0);
});

it('allows registration again once reopened', function (): void {
    closeRegistration();

    resolve(UpdateSettings::class)->handle(new SettingsData([SettingKey::RegistrationOpen->value => true]));

    $this->post(route('register'), registrationPayload())->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();
});

it('redirects the register page to login when registration is closed', function (): void {
    resolve(UpdateSettings::class)->handle(new SettingsData(['registration_open' => false]));

    $this->get(route('register'))->assertRedirect(route('login'));
    $this->get(route('login'))->assertDontSee('href="'.route('register').'"', false);
});
