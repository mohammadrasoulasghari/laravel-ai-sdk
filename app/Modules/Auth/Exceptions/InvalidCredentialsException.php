<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidCredentialsException extends DomainException
{
    public function errorCode(): string
    {
        return 'auth.invalid_credentials';
    }

    public function translationKey(): string
    {
        return 'auth::auth.errors.invalid_credentials';
    }

    public function status(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
