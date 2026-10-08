<?php

declare(strict_types=1);

use App\Modules\Blog\Http\Controllers\Api\PostController;
use App\Modules\Blog\Http\Controllers\Api\PostPublicationController;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->name('blog.')->group(function (): void {
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{slug}', [PostController::class, 'show'])->name('posts.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');
        Route::match(['put', 'patch'], 'posts/{post:id}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post:id}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('posts/{post:id}/publication', [PostPublicationController::class, 'store'])->name('posts.publish');
        Route::delete('posts/{post:id}/publication', [PostPublicationController::class, 'destroy'])->name('posts.unpublish');
    });
});
