<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Modules\Blog\Actions\CreatePost;
use App\Modules\Blog\Actions\DeletePost;
use App\Modules\Blog\Actions\UpdatePost;
use App\Modules\Blog\Data\PostFiltersData;
use App\Modules\Blog\Http\Requests\PostRequest;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Queries\AdminPostsQuery;
use App\Modules\Blog\Queries\CategoriesQuery;
use App\Modules\Users\Models\User;
use App\Shared\Http\Flash;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PostController
{
    public function index(Request $request, AdminPostsQuery $query): View
    {
        Gate::authorize('viewAny', Post::class);

        $search = $request->string('search')->trim()->toString();

        return view('blog::admin.posts.index', [
            'posts' => $query->handle(new PostFiltersData(search: $search === '' ? null : $search)),
            'search' => $search,
        ]);
    }

    public function create(CategoriesQuery $categories): View
    {
        Gate::authorize('create', Post::class);

        return view('blog::admin.posts.form', ['post' => null, 'categoryOptions' => $categories->options()]);
    }

    public function store(PostRequest $request, CreatePost $createPost, #[CurrentUser] User $author): RedirectResponse
    {
        $post = $createPost->handle($request->toData(), $author);

        return to_route('admin.blog.posts.edit', $post)->with(Flash::success(__('blog::posts.created')));
    }

    public function edit(Post $post, CategoriesQuery $categories): View
    {
        Gate::authorize('update', $post);

        return view('blog::admin.posts.form', ['post' => $post, 'categoryOptions' => $categories->options()]);
    }

    public function update(PostRequest $request, Post $post, UpdatePost $updatePost): RedirectResponse
    {
        $updatePost->handle($post, $request->toData());

        return to_route('admin.blog.posts.edit', $post)->with(Flash::success(__('blog::posts.updated')));
    }

    public function destroy(Post $post, DeletePost $deletePost): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $deletePost->handle($post);

        return to_route('admin.blog.posts.index')->with(Flash::success(__('blog::posts.deleted')));
    }
}
