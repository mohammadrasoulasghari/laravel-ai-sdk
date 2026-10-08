<?php

declare(strict_types=1);

use App\Shared\Http\Controllers\SwitchLocaleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('/locale/{locale}', SwitchLocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale.switch');
