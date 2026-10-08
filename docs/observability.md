# Observability

## Logs

- Channel stack from `LOG_STACK`: `daily` locally, **`json`** in production (one JSON object per line on stderr/file, ready for Loki/ELK/CloudWatch).
- Every record carries `correlation_id` (from `X-Request-Id`, via Laravel `Context`), plus `user_id` when authenticated. Context propagates into queued jobs automatically, so a web request and the jobs it dispatched share one id.
- Sensitive data: passwords, tokens, `Authorization`/`Cookie` headers are never logged. Request input is never logged wholesale. `#[\SensitiveParameter]` marks secrets in DTOs so they are redacted from stack traces.
- Log with context, not string concatenation: `Log::info('Post published', ['post_id' => $id])`.

## Errors

- `bootstrap/app.php` → `withExceptions()` is the single place for reporting/rendering rules.
- `DomainException`s are **not** reported (they are expected business outcomes) — they are rendered.
- Error tracker hook: set `ERROR_REPORTING_DSN` and install the vendor SDK (e.g. `composer require sentry/sentry-laravel`, then follow its Laravel 13 instructions, which register `Integration::handles($exceptions)` inside `withExceptions`). Nothing else in the app needs to change. See [`add-ons/error-tracking.md`](add-ons/error-tracking.md).

## Health endpoints

| Endpoint | Purpose | Checks |
|---|---|---|
| `GET /up` | liveness (framework built-in) | app boots |
| `GET /health/ready` | readiness for load balancers / k8s | database query, cache read/write, queue connection; returns `200 {"status":"ok","checks":{…}}` or `503` |

Both are excluded from rate limiting and session handling.

## Dashboards

| Tool | Where | Access |
|---|---|---|
| Laravel Pulse | `/pulse` | `system.tools.view` permission (local: anyone). Production-ready config in `config/pulse.php`; ingest via Redis in production (`PULSE_INGEST_DRIVER=redis` + `php artisan pulse:work`). |
| Horizon | `/horizon` | `system.tools.view` |
| Telescope | `/telescope` | **local only** — the package is a dev dependency, not discovered, and registered only when `APP_ENV=local`. |
| Mailpit | `http://localhost:8025` | local Sail only |
