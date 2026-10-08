<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Contracts\UserRegistrar;
use App\Modules\Users\Data\NewUserData;
use App\Modules\Users\Models\User;
use Illuminate\Database\ConnectionInterface;

final readonly class CreateUser implements UserRegistrar
{
    public function __construct(private ConnectionInterface $db) {}

    public function handle(NewUserData $data): User
    {
        return $this->db->transaction(static function () use ($data): User {
            $user = User::query()->create([
                'name' => $data->name,
                'email' => mb_strtolower($data->email),
                'password' => $data->password,
            ]);

            $user->assignRole(Role::Member->value);

            return $user;
        });
    }

    public function register(NewUserData $data): User
    {
        return $this->handle($data);
    }
}
