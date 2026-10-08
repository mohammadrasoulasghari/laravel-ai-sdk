<?php

declare(strict_types=1);

namespace App\Modules\Blog\Queries;

use App\Modules\Blog\Data\PostFiltersData;
use App\Modules\Blog\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/** Public listing: published posts only, with category and author eager-loaded. */
final readonly class PublishedPostsQuery
{
    /** @return LengthAwarePaginator<int, Post> */
    public function handle(PostFiltersData $filters): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->with(['category', 'author'])
            ->when($filters->categorySlug, static fn (Builder $query, string $slug): Builder => $query
                ->whereRelation('category', 'slug', $slug))
            ->when($filters->search, static fn (Builder $query, string $search): Builder => $query
                ->where(static fn (Builder $q): Builder => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")))
            ->orderBy($filters->sort->column(), $filters->sort->direction())
            ->orderByDesc('id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }
}
