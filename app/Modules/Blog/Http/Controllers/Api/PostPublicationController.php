<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers\Api;

use App\Modules\Blog\Actions\PublishPost;
use App\Modules\Blog\Actions\UnpublishPost;
use App\Modules\Blog\Http\Resources\PostResource;
use App\Modules\Blog\Models\Post;
use Illuminate\Support\Facades\Gate;

final class PostPublicationController
{
    /** Publish a draft post now. */
    public function store(Post $post, PublishPost $publish): PostResource
    {
        Gate::authorize('publish', $post);

        return new PostResource($publish->handle($post)->load(['category', 'author']));
    }

    /** Move a published post back to draft. */
    public function destroy(Post $post, UnpublishPost $unpublish): PostResource
    {
        Gate::authorize('publish', $post);

        return new PostResource($unpublish->handle($post)->load(['category', 'author']));
    }
}
