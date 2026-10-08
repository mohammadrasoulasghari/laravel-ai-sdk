<?php

declare(strict_types=1);

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Models\User;
use Laravel\Sanctum\Sanctum;

use function Tests\userWithRole;

it('returns 401 problem+json when unauthenticated', function (): void {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('status', 401);
});

it('rejects an invalid bearer token', function (): void {
    $this->withToken('1|not-a-real-token')->getJson('/api/v1/me')->assertUnauthorized();
});

it('returns the authenticated user with roles', function (): void {
    $user = userWithRole(Role::Admin->value);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', $user->name)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.email_verified', true)
        ->assertJsonPath('data.roles', [Role::Admin->value])
        ->assertJsonPath('data.created_at', $user->created_at->toIso8601String())
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token');
});

it('reports unverified users and empty roles', function (): void {
    Sanctum::actingAs(User::factory()->unverified()->create());

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.email_verified', false)
        ->assertJsonPath('data.roles', []);
});

it('works with a real personal access token', function (): void {
    $user = userWithRole(Role::Member->value);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.roles', [Role::Member->value]);
});
