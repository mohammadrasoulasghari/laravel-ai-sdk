<?php

declare(strict_types=1);

namespace App\Modules\Roles\Http\Controllers\Admin;

use App\Modules\Roles\Actions\UpdateRolePermissions;
use App\Modules\Roles\Http\Requests\UpdateRolePermissionsRequest;
use App\Modules\Roles\Queries\PermissionNamesQuery;
use App\Modules\Roles\Queries\RolesIndexQuery;
use App\Shared\Http\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class RoleController
{
    public function index(RolesIndexQuery $query): View
    {
        Gate::authorize('viewAny', Role::class);

        return view('roles::admin.index', ['roles' => $query->handle()]);
    }

    public function edit(Role $role, PermissionNamesQuery $permissions): View
    {
        Gate::authorize('update', $role);

        return view('roles::admin.edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions->handle(),
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role, UpdateRolePermissions $update): RedirectResponse
    {
        $update->handle($role, $request->toData());

        return to_route('admin.roles.index')->with(Flash::success(__('roles::roles.updated')));
    }
}
