<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Shared\Exceptions\DomainException;

/** Wraps a Password broker status such as "passwords.token" (also its translation key). */
final class PasswordResetFailedException extends DomainException
{
    public function __construct(public readonly string $brokerStatus)
    {
        parent::__construct("Password reset failed: {$brokerStatus}.");
    }

    public function errorCode(): string
    {
        return 'auth.password_reset_failed';
    }

    public function translationKey(): string
    {
        return $this->brokerStatus;
    }
}
