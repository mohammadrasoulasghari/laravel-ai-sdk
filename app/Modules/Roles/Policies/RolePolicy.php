<?php

declare(strict_types=1);

namespace App\Modules\Roles\Policies;

use App\Modules\Roles\Enums\RolePermission;
use App\Modules\Users\Models\User;

final class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(RolePermission::Manage->value);
    }

    public function update(User $actor): bool
    {
        return $actor->can(RolePermission::Manage->value);
    }
}
