<?php

declare(strict_types=1);

namespace App\Shared\Auth;

use BackedEnum;

/**
 * Modules declare their permissions here (in their provider) together with the
 * roles that get them by default. `php artisan permissions:sync` (Roles module)
 * writes them to the database idempotently.
 */
final class PermissionRegistry
{
    /** @var array<string, list<string>> permission => default role names */
    private array $permissions = [];

    /**
     * @param  list<BackedEnum>  $permissions
     * @param  list<string>  $defaultRoles
     */
    public function register(array $permissions, array $defaultRoles = []): void
    {
        foreach ($permissions as $permission) {
            $name = (string) $permission->value;
            $this->permissions[$name] = array_values(array_unique([...$this->permissions[$name] ?? [], ...$defaultRoles]));
        }
    }

    /** @return array<string, list<string>> */
    public function all(): array
    {
        ksort($this->permissions);

        return $this->permissions;
    }
}
