<?php

declare(strict_types=1);

namespace App\Modules\Users\Database\Seeders;

use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Database\Factories\UserFactory;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Seeder;

/** Creates the local super-admin (ADMIN_EMAIL / ADMIN_PASSWORD) and demo members. */
final class UsersSeeder extends Seeder
{
    public function __construct(private readonly Config $config) {}

    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => $this->config->string('starter.admin.email')],
            ['name' => 'Admin', 'password' => $this->config->string('starter.admin.password'), 'email_verified_at' => now()],
        );
        $admin->syncRoles([Role::SuperAdmin->value]);

        if (app()->environment('local')) {
            UserFactory::new()->count(10)->create()->each(static fn (User $user): User => $user->assignRole(Role::Member->value));
        }
    }
}
