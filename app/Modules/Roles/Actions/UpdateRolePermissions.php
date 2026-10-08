<?php

declare(strict_types=1);

namespace App\Modules\Roles\Actions;

use App\Modules\Roles\Data\RolePermissionsData;
use App\Modules\Roles\Enums\Role as RoleName;
use App\Modules\Roles\Exceptions\SuperAdminRoleIsImmutableException;
use Spatie\Permission\Models\Role;

final readonly class UpdateRolePermissions
{
    /** @throws SuperAdminRoleIsImmutableException */
    public function handle(Role $role, RolePermissionsData $data): Role
    {
        if ($role->name === RoleName::SuperAdmin->value) {
            throw new SuperAdminRoleIsImmutableException('The super-admin role implicitly has every permission.');
        }

        return $role->syncPermissions($data->permissions);
    }
}
