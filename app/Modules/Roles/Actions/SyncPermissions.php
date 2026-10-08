<?php

declare(strict_types=1);

namespace App\Modules\Roles\Actions;

use App\Modules\Roles\Enums\Role as RoleName;
use App\Shared\Auth\PermissionRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotently creates built-in roles and every registered permission, and grants
 * default permissions. Never revokes grants an admin added manually.
 */
final readonly class SyncPermissions
{
    public function __construct(
        private PermissionRegistry $registry,
        private PermissionRegistrar $registrar,
    ) {}

    /** @return int Number of registered permissions. */
    public function handle(): int
    {
        $this->registrar->forgetCachedPermissions();

        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        foreach ($this->registry->all() as $permission => $roles) {
            Permission::findOrCreate($permission, 'web');

            foreach ($roles as $role) {
                Role::findOrCreate($role, 'web')->givePermissionTo($permission);
            }
        }

        $this->registrar->forgetCachedPermissions();

        return count($this->registry->all());
    }
}
