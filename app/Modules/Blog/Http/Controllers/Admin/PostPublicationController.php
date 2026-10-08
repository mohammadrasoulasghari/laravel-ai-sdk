<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Modules\Blog\Actions\PublishPost;
use App\Modules\Blog\Actions\UnpublishPost;
use App\Modules\Blog\Models\Post;
use App\Shared\Http\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class PostPublicationController
{
    public function store(Post $post, PublishPost $publish): RedirectResponse
    {
        Gate::authorize('publish', $post);

        $publish->handle($post);

        return back()->with(Flash::success(__('blog::posts.published')));
    }

    public function destroy(Post $post, UnpublishPost $unpublish): RedirectResponse
    {
        Gate::authorize('publish', $post);

        $unpublish->handle($post);

        return back()->with(Flash::success(__('blog::posts.unpublished')));
    }
}
