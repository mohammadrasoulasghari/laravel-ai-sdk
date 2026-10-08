<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Modules\Blog\Actions\CreateCategory;
use App\Modules\Blog\Actions\DeleteCategory;
use App\Modules\Blog\Actions\UpdateCategory;
use App\Modules\Blog\Http\Requests\CategoryRequest;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Queries\CategoriesQuery;
use App\Shared\Http\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class CategoryController
{
    public function index(CategoriesQuery $categories): View
    {
        Gate::authorize('viewAny', Category::class);

        return view('blog::admin.categories.index', ['categories' => $categories->handle()]);
    }

    public function store(CategoryRequest $request, CreateCategory $create): RedirectResponse
    {
        $create->handle($request->toData());

        return to_route('admin.blog.categories.index')->with(Flash::success(__('blog::categories.created')));
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('blog::admin.categories.edit', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category, UpdateCategory $update): RedirectResponse
    {
        $update->handle($category, $request->toData());

        return to_route('admin.blog.categories.index')->with(Flash::success(__('blog::categories.updated')));
    }

    public function destroy(Category $category, DeleteCategory $delete): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $delete->handle($category);

        return to_route('admin.blog.categories.index')->with(Flash::success(__('blog::categories.deleted')));
    }
}
