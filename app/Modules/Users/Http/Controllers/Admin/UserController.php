<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Admin;

use App\Modules\Roles\Contracts\RoleCatalog;
use App\Modules\Users\Actions\DeleteUser;
use App\Modules\Users\Actions\UpdateUser;
use App\Modules\Users\Data\UserFiltersData;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Models\User;
use App\Modules\Users\Queries\UsersIndexQuery;
use App\Shared\Http\Flash;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class UserController
{
    public function index(Request $request, UsersIndexQuery $query): View
    {
        Gate::authorize('viewAny', User::class);

        return view('users::admin.index', [
            'users' => $query->handle(new UserFiltersData(search: $request->string('search')->trim()->toString() ?: null)),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function edit(User $user, RoleCatalog $roles): View
    {
        Gate::authorize('update', $user);

        return view('users::admin.edit', [
            'user' => $user->load('roles'),
            'roleOptions' => $roles->options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        $updateUser->handle($user, $request->toData());

        return to_route('admin.users.index')->with(Flash::success(__('users::users.updated')));
    }

    public function destroy(User $user, #[CurrentUser] User $actor, DeleteUser $deleteUser): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $deleteUser->handle($user, $actor);

        return to_route('admin.users.index')->with(Flash::success(__('users::users.deleted')));
    }
}
