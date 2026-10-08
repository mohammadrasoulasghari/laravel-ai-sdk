<?php

declare(strict_types=1);

namespace App\Modules\Blog\Queries;

use App\Modules\Blog\Data\PostFiltersData;
use App\Modules\Blog\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/** Admin listing: every post regardless of status, newest first. */
final readonly class AdminPostsQuery
{
    /** @return LengthAwarePaginator<int, Post> */
    public function handle(PostFiltersData $filters): LengthAwarePaginator
    {
        return Post::query()
            ->with(['category', 'author'])
            ->when($filters->search, static fn (Builder $query, string $search): Builder => $query
                ->where('title', 'like', "%{$search}%"))
            ->latest()
            ->orderByDesc('id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }
}
