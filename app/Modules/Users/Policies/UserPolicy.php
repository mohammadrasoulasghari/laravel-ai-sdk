<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(UserPermission::Manage->value);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->can(UserPermission::Manage->value) && $this->mayTouch($actor, $user);
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->can(UserPermission::Manage->value) && $this->mayTouch($actor, $user);
    }

    /** Only super admins may modify other super admins (prevents takeover via email change, demotion or deletion). */
    private function mayTouch(User $actor, User $user): bool
    {
        return ! $user->hasRole(Role::SuperAdmin->value) || $actor->hasRole(Role::SuperAdmin->value);
    }
}
