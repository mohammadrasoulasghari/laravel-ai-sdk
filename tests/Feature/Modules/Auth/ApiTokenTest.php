<?php

declare(strict_types=1);

use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Users\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('issues a personal access token', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->postJson('/api/v1/auth/tokens', [
        'email' => 'Jane@Example.com',
        'password' => 'password',
        'device_name' => 'iPhone',
    ])
        ->assertCreated()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.expires_at', null)
        ->assertJsonStructure(['data' => ['token', 'token_type', 'expires_at']]);

    $plain = $response->json('data.token');

    expect($plain)->toBeString()
        ->and($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->sole()->name)->toBe('iPhone');

    $this->withToken($plain)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $user->id);
});

it('sets an expiry when sanctum expiration is configured', function (): void {
    config(['sanctum.expiration' => 60]);
    $this->freezeTime();
    User::factory()->create(['email' => 'jane@example.com']);

    $this->postJson('/api/v1/auth/tokens', ['email' => 'jane@example.com', 'password' => 'password', 'device_name' => 'cli'])
        ->assertCreated()
        ->assertJsonPath('data.expires_at', now()->addMinutes(60)->format(DATE_ATOM));

    expect(PersonalAccessToken::query()->sole()->expires_at?->toDateTimeString())->toBe(now()->addMinutes(60)->toDateTimeString());
});

it('rejects invalid credentials with problem+json', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    $this->postJson('/api/v1/auth/tokens', ['email' => 'jane@example.com', 'password' => 'wrong', 'device_name' => 'cli'])
        ->assertStatus(422)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'urn:problem:auth.invalid_credentials')
        ->assertJsonPath('status', 422)
        ->assertJsonPath('detail', __('auth::auth.errors.invalid_credentials'))
        ->assertJsonPath('instance', '/api/v1/auth/tokens');

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

it('rejects an unknown email the same way', function (): void {
    $this->postJson('/api/v1/auth/tokens', ['email' => 'nobody@example.com', 'password' => 'password', 'device_name' => 'cli'])
        ->assertStatus(422)
        ->assertJsonPath('type', 'urn:problem:auth.invalid_credentials');
});

it('validates the token request', function (array $payload, string $field): void {
    $this->postJson('/api/v1/auth/tokens', [
        'email' => 'jane@example.com',
        'password' => 'password',
        'device_name' => 'cli',
        ...$payload,
    ])
        ->assertStatus(422)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'about:blank')
        ->assertJsonValidationErrors($field);
})->with([
    'missing email' => [['email' => ''], 'email'],
    'missing password' => [['password' => ''], 'password'],
    'missing device name' => [['device_name' => ''], 'device_name'],
    'long device name' => [['device_name' => str_repeat('a', 101)], 'device_name'],
]);

it('throttles token requests after too many failures', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    foreach (range(1, AuthenticateUser::MAX_ATTEMPTS) as $attempt) {
        $this->postJson('/api/v1/auth/tokens', ['email' => 'jane@example.com', 'password' => 'wrong', 'device_name' => 'cli'])
            ->assertStatus(422);
    }

    $this->postJson('/api/v1/auth/tokens', ['email' => 'jane@example.com', 'password' => 'password', 'device_name' => 'cli'])
        ->assertStatus(429)
        ->assertJsonPath('type', 'urn:problem:auth.too_many_attempts');

    expect(PersonalAccessToken::query()->count())->toBe(0);
});

it('revokes the current token', function (): void {
    $user = User::factory()->create();
    $current = $user->createToken('current')->plainTextToken;
    $user->createToken('other');

    $this->withToken($current)->deleteJson('/api/v1/auth/tokens/current')->assertNoContent();

    expect($user->tokens()->pluck('name')->all())->toBe(['other']);

    $this->app['auth']->forgetGuards();

    $this->withToken($current)->getJson('/api/v1/me')->assertUnauthorized();
});

it('requires a token to revoke', function (): void {
    $this->deleteJson('/api/v1/auth/tokens/current')
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/problem+json');
});
