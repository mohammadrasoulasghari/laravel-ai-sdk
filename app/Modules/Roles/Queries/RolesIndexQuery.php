<?php

declare(strict_types=1);

namespace App\Modules\Roles\Queries;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final readonly class RolesIndexQuery
{
    /** @return Collection<int, Role> */
    public function handle(): Collection
    {
        return Role::query()->with('permissions')->withCount('users')->orderBy('name')->get();
    }
}
