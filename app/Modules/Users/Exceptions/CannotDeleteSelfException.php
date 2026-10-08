<?php

declare(strict_types=1);

namespace App\Modules\Users\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class CannotDeleteSelfException extends DomainException
{
    public function errorCode(): string
    {
        return 'users.cannot_delete_self';
    }

    public function translationKey(): string
    {
        return 'users::users.errors.cannot_delete_self';
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
