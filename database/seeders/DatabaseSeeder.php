<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Blog\Database\Seeders\BlogSeeder;
use App\Modules\Roles\Database\Seeders\RolesSeeder;
use App\Modules\Users\Database\Seeders\UsersSeeder;
use Illuminate\Database\Seeder;

/**
 * Calls each module's seeder in dependency order. `make:module` does not edit this
 * file — add new module seeders here by hand.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
