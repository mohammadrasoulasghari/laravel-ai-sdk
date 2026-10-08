<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(UserPermission::Manage->value);
    }

    public function update(User $actor): bool
    {
        return $actor->can(UserPermission::Manage->value);
    }

    public function delete(User $actor): bool
    {
        return $actor->can(UserPermission::Manage->value);
    }
}
