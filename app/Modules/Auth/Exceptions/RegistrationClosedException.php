<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class RegistrationClosedException extends DomainException
{
    public function errorCode(): string
    {
        return 'auth.registration_closed';
    }

    public function translationKey(): string
    {
        return 'auth::auth.errors.registration_closed';
    }

    public function status(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
