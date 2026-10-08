<?php

declare(strict_types=1);

namespace App\Modules\Roles\Enums;

enum RolePermission: string
{
    case Manage = 'roles.manage';
}
