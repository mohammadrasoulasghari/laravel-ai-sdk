<?php

declare(strict_types=1);

use App\Shared\Auth\SystemAbility;
use App\Shared\Exceptions\DomainException;
use App\Shared\Http\Flash;
use App\Shared\Http\Middleware\AssignCorrelationId;
use App\Shared\Http\Middleware\SetLocale;
use App\Shared\Http\Problems\ProblemDetailsFactory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AssignCorrelationId::class);
        // Global run negotiates Accept-Language (also for unmatched routes/404s);
        // the web run re-resolves after the session has started.
        $middleware->append(SetLocale::class);
        $middleware->web(append: [SetLocale::class]);
        $middleware->throttleApi('api');
        // Every /admin route: authenticated, verified, and holding admin.access.
        $middleware->group('admin', ['auth', 'verified', 'can:'.SystemAbility::AccessAdmin->value]);
        $middleware->redirectGuestsTo(fn (): string => route('login'));
        $middleware->redirectUsersTo(fn (): string => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );

        // API: every error is RFC 9457 problem+json (ADR-0010).
        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return resolve(ProblemDetailsFactory::class)->fromThrowable($e, $request)->toResponse($request);
        });

        // Web: business-rule violations go back to the form with a flash error.
        $exceptions->render(fn (DomainException $e, Request $request): RedirectResponse => back()
            ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
            ->with(Flash::error(__($e->translationKey(), $e->translationParameters()))));

        $exceptions->dontReport([DomainException::class]);
        $exceptions->dontFlash(['current_password', 'password', 'password_confirmation']);
    })->create();
