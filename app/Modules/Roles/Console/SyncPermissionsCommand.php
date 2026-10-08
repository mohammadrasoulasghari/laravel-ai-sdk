<?php

declare(strict_types=1);

namespace App\Modules\Roles\Console;

use App\Modules\Roles\Actions\SyncPermissions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'permissions:sync', description: 'Create built-in roles and all module permissions (idempotent).')]
final class SyncPermissionsCommand extends Command
{
    public function handle(SyncPermissions $sync): int
    {
        $count = $sync->handle();

        $this->components->info("Synced {$count} permissions.");

        return self::SUCCESS;
    }
}
