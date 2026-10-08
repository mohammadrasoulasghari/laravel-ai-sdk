<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me');
