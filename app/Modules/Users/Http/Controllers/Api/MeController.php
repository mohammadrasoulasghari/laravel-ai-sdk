<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Modules\Users\Http\Resources\UserResource;
use App\Modules\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final class MeController
{
    /** The authenticated user. */
    public function __invoke(#[CurrentUser] User $user): UserResource
    {
        return new UserResource($user->load('roles'));
    }
}
