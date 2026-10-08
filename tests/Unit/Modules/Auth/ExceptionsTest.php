<?php

declare(strict_types=1);

use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\PasswordResetFailedException;
use App\Modules\Auth\Exceptions\RegistrationClosedException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use App\Shared\Exceptions\DomainException;

it('describes each auth exception', function (DomainException $exception, string $code, int $status, string $message): void {
    expect($exception->errorCode())->toBe($code)
        ->and($exception->status())->toBe($status)
        ->and(__($exception->translationKey(), $exception->translationParameters()))->toBe($message);
})->with([
    'invalid credentials' => fn (): array => [new InvalidCredentialsException('x'), 'auth.invalid_credentials', 422, __('auth::auth.errors.invalid_credentials')],
    'registration closed' => fn (): array => [new RegistrationClosedException('x'), 'auth.registration_closed', 403, __('auth::auth.errors.registration_closed')],
    'too many attempts' => fn (): array => [new TooManyLoginAttemptsException(42), 'auth.too_many_attempts', 429, 'Too many login attempts. Please try again in 42 seconds.'],
    'reset failed' => fn (): array => [new PasswordResetFailedException('passwords.token'), 'auth.password_reset_failed', 422, __('passwords.token')],
]);

it('exposes retry and broker details', function (): void {
    $throttled = new TooManyLoginAttemptsException(30);
    $reset = new PasswordResetFailedException('passwords.user');

    expect($throttled->retryAfterSeconds)->toBe(30)
        ->and($throttled->translationParameters())->toBe(['seconds' => 30])
        ->and($throttled->getMessage())->toContain('30s')
        ->and($reset->brokerStatus)->toBe('passwords.user')
        ->and($reset->translationKey())->toBe('passwords.user')
        ->and($reset->getMessage())->toContain('passwords.user');
});

it('translates auth errors to Persian', function (): void {
    expect(__('auth::auth.errors.invalid_credentials', locale: 'fa'))->not->toBe(__('auth::auth.errors.invalid_credentials', locale: 'en'))
        ->and(__('auth.throttle', ['seconds' => 5], 'fa'))->toContain('5');
});
