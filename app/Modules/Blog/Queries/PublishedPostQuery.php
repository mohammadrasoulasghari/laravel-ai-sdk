<?php

declare(strict_types=1);

namespace App\Modules\Blog\Queries;

use App\Modules\Blog\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/** A single public post by slug; drafts and scheduled posts are "not found". */
final readonly class PublishedPostQuery
{
    /** @throws ModelNotFoundException<Post> */
    public function handle(string $slug): Post
    {
        return Post::query()->published()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
    }
}
