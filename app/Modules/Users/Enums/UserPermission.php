<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

enum UserPermission: string
{
    case Manage = 'users.manage';
}
