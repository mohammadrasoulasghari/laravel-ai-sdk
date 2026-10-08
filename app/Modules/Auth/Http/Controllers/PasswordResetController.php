<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Actions\ResetPassword;
use App\Modules\Auth\Actions\SendPasswordResetLink;
use App\Modules\Auth\Http\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Http\Requests\ResetPasswordRequest;
use App\Shared\Http\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PasswordResetController
{
    public function request(): View
    {
        return view('auth::forgot-password');
    }

    public function email(ForgotPasswordRequest $request, SendPasswordResetLink $send): RedirectResponse
    {
        $send->handle($request->string('email')->toString());

        return back()->with(Flash::info(__('auth::auth.reset_link_sent')));
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth::reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(ResetPasswordRequest $request, ResetPassword $reset): RedirectResponse
    {
        $reset->handle($request->toData());

        return to_route('login')->with(Flash::success(__('passwords.reset')));
    }
}
