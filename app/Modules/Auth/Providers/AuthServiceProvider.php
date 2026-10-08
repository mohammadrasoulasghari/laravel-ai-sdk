<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Shared\Providers\ModuleServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class AuthServiceProvider extends ModuleServiceProvider
{
    protected function bootModule(): void
    {
        // Route-level guard for every credential endpoint (login, register, reset, tokens).
        // AuthenticateUser adds a stricter per-email+IP limit on top.
        RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute($this->app->make(Config::class)->integer('starter.rate_limits.auth'))->by('auth:'.$request->ip()));
    }
}
