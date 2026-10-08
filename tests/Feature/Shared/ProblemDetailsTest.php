<?php

declare(strict_types=1);

use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Route::middleware('api')->prefix('api/v1/_test')->group(function (): void {
        Route::get('/validation', fn () => throw ValidationException::withMessages(['title' => 'Required.']));
        Route::get('/domain', fn () => throw new class('dev message') extends DomainException
        {
            public function errorCode(): string
            {
                return 'test.rule_broken';
            }

            public function translationKey(): string
            {
                return 'problems.validation_failed';
            }

            public function status(): int
            {
                return 409;
            }
        });
        Route::get('/crash', fn () => throw new RuntimeException('secret internals'));
        Route::get('/auth', fn (): string => 'never')->middleware('auth:sanctum');
    });
});

it('renders validation errors as problem+json', function (): void {
    $this->getJson('/api/v1/_test/validation')
        ->assertStatus(422)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'about:blank')
        ->assertJsonPath('status', 422)
        ->assertJsonPath('errors.title.0', 'Required.')
        ->assertJsonPath('instance', '/api/v1/_test/validation')
        ->assertJsonStructure(['title', 'correlation_id']);
});

it('renders domain exceptions with a urn type and translated detail', function (): void {
    $this->getJson('/api/v1/_test/domain')
        ->assertStatus(409)
        ->assertJsonPath('type', 'urn:problem:test.rule_broken')
        ->assertJsonPath('detail', __('problems.validation_failed'));
});

it('hides internals of unexpected errors when debug is off', function (): void {
    config(['app.debug' => false]);

    $this->getJson('/api/v1/_test/crash')
        ->assertStatus(500)
        ->assertJsonMissingPath('detail')
        ->assertJsonPath('title', __('problems.status.500'));
});

it('returns 401 for unauthenticated API calls', function (): void {
    $this->getJson('/api/v1/_test/auth')->assertStatus(401)->assertJsonPath('status', 401);
});

it('returns 404 problem for unknown API routes', function (): void {
    $this->getJson('/api/v1/does-not-exist')->assertStatus(404)->assertJsonPath('status', 404);
});

it('translates problem titles to the requested locale', function (): void {
    $this->getJson('/api/v1/does-not-exist', ['Accept-Language' => 'fa'])
        ->assertJsonPath('title', 'یافت نشد')
        ->assertHeader('Content-Language', 'fa');
});
