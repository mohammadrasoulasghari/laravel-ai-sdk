<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Hides developer-only routes (style guide) outside the local environment. */
final readonly class LocalOnly
{
    public function __construct(private Application $app) {}

    /** @param  Closure(Request): Response  $next */
    public function handle(Request $request, Closure $next): Response
    {
        throw_unless($this->app->environment('local', 'testing'), NotFoundHttpException::class);

        return $next($request);
    }
}
