<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/tokens', [TokenController::class, 'store'])->middleware('throttle:auth')->name('auth.tokens.store');
Route::delete('/auth/tokens/current', [TokenController::class, 'destroy'])->middleware('auth:sanctum')->name('auth.tokens.destroy');
