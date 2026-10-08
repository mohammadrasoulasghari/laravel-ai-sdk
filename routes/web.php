<?php

declare(strict_types=1);

use App\Shared\Http\Controllers\StyleGuideController;
use App\Shared\Http\Controllers\SwitchLocaleController;
use App\Shared\Http\Middleware\LocalOnly;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('/locale/{locale}', SwitchLocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale.switch');

Route::get('/_styleguide', StyleGuideController::class)
    ->middleware(LocalOnly::class)
    ->name('styleguide');
