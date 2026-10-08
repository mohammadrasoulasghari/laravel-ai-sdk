# Error tracking (Sentry)

## When to use

Any production deployment. Logs tell you what happened; an error tracker groups exceptions, alerts you and shows stack traces with request context.

## Recommended package

**`sentry/sentry-laravel`** — verify the current version and Laravel 13 instructions with the `researcher` agent.

The kit already reserves the hook: `.env.example` has `ERROR_REPORTING_DSN=` (empty = disabled) and [`docs/observability.md`](../observability.md) describes the exception pipeline. No module is needed — this is infrastructure.

## Steps

1. Install and publish the config:
   ```bash
   composer require sentry/sentry-laravel
   php artisan sentry:publish --dsn=
   ```
2. Map the kit's generic variable to Sentry's in `config/sentry.php`, so `.env` keeps one vendor-neutral key:
   ```php
   'dsn' => env('SENTRY_LARAVEL_DSN', env('ERROR_REPORTING_DSN')),
   ```
3. Register the integration in `bootstrap/app.php`, inside the existing `withExceptions()` closure:
   ```php
   use Sentry\Laravel\Integration;

   ->withExceptions(function (Exceptions $exceptions): void {
       Integration::handles($exceptions);
       // … existing problem+json and DomainException rules stay as they are
   })
   ```
4. Set `ERROR_REPORTING_DSN` in the production environment (never commit it).

## Integration points

- `DomainException`s are excluded by `$exceptions->dontReport([DomainException::class])` — expected business outcomes do not create Sentry issues.
- Add the correlation id as a tag so Sentry events link to log lines (e.g. in a `beforeSend` callback or `Sentry\configureScope` in a service provider, reading it from Laravel `Context`).
- Privacy: keep `send_default_pii` off; passwords are already in `dontFlash`.
- CSP: only needed if you add the browser SDK — then extend `connect-src` in `App\Shared\Http\Middleware\SecurityHeaders`.

## Tests to add

- With `ERROR_REPORTING_DSN` empty the app boots and the suite stays green (no network).
- A feature test that a `DomainException` subclass is not reported (`Exceptions::fake()` + `Exceptions::assertNotReported(...)`) and a generic exception is.
