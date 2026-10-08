<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers\Api;

use App\Modules\Blog\Actions\CreatePost;
use App\Modules\Blog\Actions\DeletePost;
use App\Modules\Blog\Actions\UpdatePost;
use App\Modules\Blog\Http\Requests\PostIndexRequest;
use App\Modules\Blog\Http\Requests\PostRequest;
use App\Modules\Blog\Http\Resources\PostResource;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Queries\PublishedPostQuery;
use App\Modules\Blog\Queries\PublishedPostsQuery;
use App\Modules\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/** Same Actions/Queries as the web controllers — only the output differs. */
final class PostController
{
    /**
     * List published posts.
     *
     * @unauthenticated
     */
    public function index(PostIndexRequest $request, PublishedPostsQuery $posts): AnonymousResourceCollection
    {
        return PostResource::collection($posts->handle($request->toData()));
    }

    /**
     * Show a published post by slug.
     *
     * @unauthenticated
     */
    public function show(string $slug, PublishedPostQuery $post): PostResource
    {
        return new PostResource($post->handle($slug));
    }

    /** Create a draft post. */
    public function store(PostRequest $request, CreatePost $createPost, #[CurrentUser] User $author): PostResource
    {
        return new PostResource($createPost->handle($request->toData(), $author)->load(['category', 'author']));
    }

    /** Update a post (drafts and published). */
    public function update(PostRequest $request, Post $post, UpdatePost $updatePost): PostResource
    {
        return new PostResource($updatePost->handle($post, $request->toData())->load(['category', 'author']));
    }

    /** Delete a post. */
    public function destroy(Post $post, DeletePost $deletePost): Response
    {
        Gate::authorize('delete', $post);

        $deletePost->handle($post);

        return response()->noContent();
    }
}
