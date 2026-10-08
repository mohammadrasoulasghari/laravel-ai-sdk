<?php

declare(strict_types=1);

use App\Modules\Blog\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Modules\Blog\Http\Controllers\Admin\PostController as AdminPostController;
use App\Modules\Blog\Http\Controllers\Admin\PostPublicationController;
use App\Modules\Blog\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/blog', [PostController::class, 'index'])->name('blog.posts.index');
Route::get('/blog/{slug}', [PostController::class, 'show'])->name('blog.posts.show');

Route::middleware('admin')->prefix('admin/blog')->name('admin.blog.')->group(function (): void {
    Route::resource('posts', AdminPostController::class)->except('show');
    Route::post('posts/{post}/publication', [PostPublicationController::class, 'store'])->name('posts.publish');
    Route::delete('posts/{post}/publication', [PostPublicationController::class, 'destroy'])->name('posts.unpublish');
    Route::resource('categories', AdminCategoryController::class)->except(['show', 'create']);
});
