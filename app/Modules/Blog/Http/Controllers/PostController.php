<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers;

use App\Modules\Blog\Http\Requests\PostIndexRequest;
use App\Modules\Blog\Queries\CategoriesQuery;
use App\Modules\Blog\Queries\PublishedPostQuery;
use App\Modules\Blog\Queries\PublishedPostsQuery;
use Illuminate\Contracts\View\View;

/** Public blog pages. */
final class PostController
{
    public function index(PostIndexRequest $request, PublishedPostsQuery $posts, CategoriesQuery $categories): View
    {
        $filters = $request->toData();

        return view('blog::posts.index', [
            'posts' => $posts->handle($filters),
            'categories' => $categories->handle(),
            'activeCategory' => $filters->categorySlug,
        ]);
    }

    public function show(string $slug, PublishedPostQuery $post): View
    {
        return view('blog::posts.show', ['post' => $post->handle($slug)]);
    }
}
