<?php

declare(strict_types=1);

namespace App\Modules\Roles\Enums;

/** Built-in roles. Super admins pass every Gate check (see RolesServiceProvider). */
enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Member = 'member';
}
