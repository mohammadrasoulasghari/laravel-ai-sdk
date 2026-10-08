<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\Admin\UserController;
use App\Modules\Users\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);
});
