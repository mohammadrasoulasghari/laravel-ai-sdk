<?php

declare(strict_types=1);

namespace App\Modules\Roles\Queries;

use Spatie\Permission\Models\Permission;

final readonly class PermissionNamesQuery
{
    /** @return list<string> */
    public function handle(): array
    {
        return array_values(Permission::query()->orderBy('name')->get()
            ->map(static fn (Permission $permission): string => $permission->name)
            ->all());
    }
}
