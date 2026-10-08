<?php

declare(strict_types=1);

namespace App\Modules\Users\Queries;

use App\Modules\Users\Data\UserFiltersData;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final readonly class UsersIndexQuery
{
    /** @return LengthAwarePaginator<int, User> */
    public function handle(UserFiltersData $filters): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($filters->search, static function (Builder $query, string $search): void {
                $query->where(static fn (Builder $q): Builder => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($filters->perPage)
            ->withQueryString();
    }
}
