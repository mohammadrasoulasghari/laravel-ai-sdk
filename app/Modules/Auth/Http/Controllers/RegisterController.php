<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Actions\RegisterUser;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use App\Shared\Http\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class RegisterController
{
    public function create(SettingsStore $settings): View|RedirectResponse
    {
        if (! $settings->boolean(SettingKey::RegistrationOpen)) {
            return to_route('login')->with(Flash::info(__('auth::auth.errors.registration_closed')));
        }

        return view('auth::register');
    }

    public function store(RegisterRequest $request, RegisterUser $register): RedirectResponse
    {
        $user = $register->handle($request->toData());

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return to_route('verification.notice');
    }
}
