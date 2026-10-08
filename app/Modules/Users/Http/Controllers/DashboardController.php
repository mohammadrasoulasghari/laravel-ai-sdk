<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Modules\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(#[CurrentUser] User $user): View
    {
        return view('users::dashboard', ['user' => $user]);
    }
}
