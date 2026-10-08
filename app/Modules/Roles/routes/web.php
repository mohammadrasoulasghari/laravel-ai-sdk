<?php

declare(strict_types=1);

use App\Modules\Roles\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);
});
