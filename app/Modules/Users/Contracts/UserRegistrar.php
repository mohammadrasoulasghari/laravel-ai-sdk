<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts;

use App\Modules\Users\Data\NewUserData;
use App\Modules\Users\Models\User;

/**
 * Public entry point for creating users from other modules (e.g. Auth registration).
 * Bound to App\Modules\Users\Actions\CreateUser.
 */
interface UserRegistrar
{
    public function register(NewUserData $data): User;
}
