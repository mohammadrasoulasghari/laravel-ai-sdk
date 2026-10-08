<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LoginController
{
    public function create(SettingsStore $settings): View
    {
        return view('auth::login', ['registrationOpen' => $settings->boolean(SettingKey::RegistrationOpen)]);
    }

    public function store(LoginRequest $request, AuthenticateUser $authenticate): RedirectResponse
    {
        $authenticate->handle($request->toData());

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
