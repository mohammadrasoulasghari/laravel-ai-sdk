<?php

declare(strict_types=1);

namespace App\Modules\Roles\Database\Seeders;

use App\Modules\Roles\Actions\SyncPermissions;
use Illuminate\Database\Seeder;

final class RolesSeeder extends Seeder
{
    public function run(SyncPermissions $sync): void
    {
        $sync->handle();
    }
}
