<?php

declare(strict_types=1);

namespace App\Modules\Blog\Policies;

use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Users\Models\User;

final class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(BlogPermission::ManagePosts->value);
    }

    public function create(User $user): bool
    {
        return $user->can(BlogPermission::ManagePosts->value);
    }

    public function update(User $user): bool
    {
        return $user->can(BlogPermission::ManagePosts->value);
    }

    public function publish(User $user): bool
    {
        return $user->can(BlogPermission::ManagePosts->value);
    }

    public function delete(User $user): bool
    {
        return $user->can(BlogPermission::ManagePosts->value);
    }
}
