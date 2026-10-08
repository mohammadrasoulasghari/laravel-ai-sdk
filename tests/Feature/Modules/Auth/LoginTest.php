<?php

declare(strict_types=1);

use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Users\Models\User;
use App\Shared\Http\Flash;
use Illuminate\Support\Str;

use function Tests\userWithRole;

it('shows the login page to guests', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(__('auth::auth.login_title'))
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false);
});

it('logs a user in with valid credentials', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

it('treats the email case-insensitively', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->post(route('login'), ['email' => 'JANE@Example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('regenerates the session id on login', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);
    $sessionId = Str::random(40);

    $this->withCookie((string) config('session.cookie'), $sessionId)
        ->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect();

    expect(session()->getId())->not->toBe($sessionId);
});

it('keeps the session id when login fails', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);
    $sessionId = Str::random(40);

    $this->withCookie((string) config('session.cookie'), $sessionId)
        ->from(route('login'))
        ->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password'])
        ->assertRedirect(route('login'));

    expect(session()->getId())->toBe($sessionId);
});

it('redirects to the intended url after login', function (): void {
    userWithRole('admin');
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->get('/admin/users')->assertRedirect(route('login'));

    $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(url('/admin/users'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials with a flash error', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    $this->from(route('login'))
        ->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password'])
        ->assertRedirect(route('login'))
        ->assertSessionHas(Flash::SESSION_KEY, [
            'type' => 'error',
            'message' => __('auth::auth.errors.invalid_credentials'),
        ])
        ->assertSessionHasInput('email', 'jane@example.com')
        ->assertSessionMissing('_old_input.password');

    $this->assertGuest();
});

it('rejects an unknown email', function (): void {
    $this->from(route('login'))
        ->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'password'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('flash.type', 'error');

    $this->assertGuest();
});

it('validates the login form', function (array $payload, string $field): void {
    $this->from(route('login'))
        ->post(route('login'), $payload)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors($field);

    $this->assertGuest();
})->with([
    'missing email' => [['password' => 'password'], 'email'],
    'invalid email' => [['email' => 'not-an-email', 'password' => 'password'], 'email'],
    'missing password' => [['email' => 'jane@example.com'], 'password'],
    'non boolean remember' => [['email' => 'jane@example.com', 'password' => 'password', 'remember' => 'nope'], 'remember'],
]);

it('shows the flash error on the login page', function (): void {
    $this->from(route('login'))
        ->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'password']);

    $this->get(route('login'))->assertSee(__('auth::auth.errors.invalid_credentials'));
});

it('locks the login after too many failed attempts', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS) as $attempt) {
        $this->from(route('login'))
            ->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password'])
            ->assertSessionHas('flash.message', __('auth::auth.errors.invalid_credentials'));
    }

    // Even the correct password is refused while locked out.
    $response = $this->from(route('login'))
        ->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('flash.type', 'error');

    expect($response->baseResponse->getSession()?->get('flash.message'))
        ->toBeString()
        ->toContain('Too many login attempts')
        ->not->toContain(':seconds');

    $this->assertGuest();
});

it('throttles per email so other accounts can still log in', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);
    $other = User::factory()->create(['email' => 'john@example.com']);

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS) as $attempt) {
        $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password']);
    }

    $this->post(route('login'), ['email' => 'john@example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($other);
});

it('clears failed attempts after a successful login', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS - 1) as $attempt) {
        $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password']);
    }

    $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password']);
    $this->assertAuthenticatedAs($user);
    $this->post(route('logout'));

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS - 1) as $attempt) {
        $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'wrong-password']);
    }

    $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

it('logs the user out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('requires authentication to log out', function (): void {
    $this->post(route('logout'))->assertRedirect(route('login'));
});

it('redirects authenticated users away from guest pages', function (string $route): void {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertRedirect(route('dashboard'));
})->with(['login', 'register', 'password.request']);

it('redirects guests to the login page', function (string $uri): void {
    $this->get($uri)->assertRedirect(route('login'));
})->with(['/dashboard', '/verify-email', '/admin', '/admin/users', '/admin/roles', '/admin/settings']);

it('sends Retry-After with API lockouts', function (): void {
    $user = User::factory()->create(['password' => 'password']);

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS) as $i) {
        $this->postJson('/api/v1/auth/tokens', ['email' => $user->email, 'password' => 'wrong', 'device_name' => 'x']);
    }

    $this->postJson('/api/v1/auth/tokens', ['email' => $user->email, 'password' => 'wrong', 'device_name' => 'x'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});
