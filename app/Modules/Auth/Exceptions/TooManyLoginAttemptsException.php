<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class TooManyLoginAttemptsException extends DomainException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct("Login locked for {$retryAfterSeconds}s after too many attempts.");
    }

    public function errorCode(): string
    {
        return 'auth.too_many_attempts';
    }

    public function translationKey(): string
    {
        return 'auth.throttle';
    }

    public function translationParameters(): array
    {
        return ['seconds' => $this->retryAfterSeconds];
    }

    public function status(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }
}
