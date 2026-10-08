<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Users\Models\User;

final readonly class RevokeCurrentToken
{
    public function handle(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
