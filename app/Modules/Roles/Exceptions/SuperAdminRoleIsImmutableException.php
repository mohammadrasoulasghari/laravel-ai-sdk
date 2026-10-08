<?php

declare(strict_types=1);

namespace App\Modules\Roles\Exceptions;

use App\Shared\Exceptions\DomainException;

final class SuperAdminRoleIsImmutableException extends DomainException
{
    public function errorCode(): string
    {
        return 'roles.super_admin_immutable';
    }

    public function translationKey(): string
    {
        return 'roles::roles.errors.super_admin_immutable';
    }
}
