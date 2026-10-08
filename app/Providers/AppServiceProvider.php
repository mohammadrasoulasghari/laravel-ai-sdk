<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $production = $this->app->isProduction();

        // Fail loudly outside production: lazy loading (N+1), silently discarded
        // attributes and missing attributes all throw.
        Model::shouldBeStrict(! $production);
        Model::automaticallyEagerLoadRelationships(false);

        // Immutable dates everywhere: no accidental mutation across layers.
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands($production);

        if ($production) {
            URL::forceHttps();
        }

        Password::defaults(static fn (): Password => $production
            ? Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8));

        Vite::useAggressivePrefetching();

        // Escape Markdown syntax inside mail notification text (prevents link injection
        // through user-supplied titles/messages in MailMessage::line()).
        Markdown::withSecuredEncoding();

        if ($production && config()->boolean('app.debug')) {
            Log::critical('APP_DEBUG is enabled in production: stack traces and environment details are exposed.');
        }

        // Behind a load balancer / TLS proxy set TRUSTED_PROXIES (comma list or "*") so
        // request IPs (rate limits) and HTTPS detection (HSTS, secure cookies) are correct.
        $proxies = config('starter.trusted_proxies');
        if (is_string($proxies) || is_array($proxies)) {
            TrustProxies::at($proxies);
        }

        $this->configureRateLimiting();
    }

    /** Named limiters; per-route limiters live in each module provider's bootModule(). */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            $id = $request->user()?->getAuthIdentifier();
            $key = is_string($id) ? 'user:'.$id : 'ip:'.$request->ip();

            return Limit::perMinute(config()->integer('starter.rate_limits.api'))->by($key);
        });
    }
}
