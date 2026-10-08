<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Models\User;
use Illuminate\Database\ConnectionInterface;

final readonly class UpdateUser
{
    public function __construct(private ConnectionInterface $db) {}

    public function handle(User $user, UpdateUserData $data): User
    {
        return $this->db->transaction(function () use ($user, $data): User {
            $emailChanged = mb_strtolower($data->email) !== $user->email;

            $user->fill(['name' => $data->name, 'email' => mb_strtolower($data->email)]);

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();
            $user->syncRoles($data->roles);

            return $user;
        });
    }
}
