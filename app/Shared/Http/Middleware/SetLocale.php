<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use App\Shared\Localization\Locales;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the request locale: session choice → Accept-Language → default.
 */
final readonly class SetLocale
{
    public const string SESSION_KEY = 'locale';

    public function __construct(
        private Locales $locales,
        private Application $app,
    ) {}

    /** @param  Closure(Request): Response  $next */
    public function handle(Request $request, Closure $next): Response
    {
        $this->app->setLocale($this->resolve($request));

        $response = $next($request);
        $response->headers->set('Content-Language', $this->app->getLocale());

        return $response;
    }

    private function resolve(Request $request): string
    {
        if ($request->hasSession()) {
            $chosen = $request->session()->get(self::SESSION_KEY);

            if (is_string($chosen) && $this->locales->isSupported($chosen)) {
                return $chosen;
            }
        }

        $preferred = $request->getPreferredLanguage(array_keys($this->locales->names()));

        if ($request->headers->has('Accept-Language') && is_string($preferred)) {
            return $preferred;
        }

        return $this->app->getLocale();
    }
}
