# ADR-0015: Content Security Policy with nonces and Alpine's `'unsafe-eval'`

- Status: Accepted
- Date: 2026-10-08

## Context
Every response should carry a Content Security Policy (OWASP Secure Headers). The UI uses the standard Alpine.js build (ADR-0009), which evaluates `x-data`, `@click`, `:class` … expressions with `new Function()` — blocked by any CSP without `'unsafe-eval'`. The base layout also has one inline script (theme bootstrap to avoid a dark-mode flash), and vendor dashboards (Horizon, Telescope, Pulse, Scramble `/docs/api`) ship their own inline scripts we do not control.

## Decision
`App\Shared\Http\Middleware\SecurityHeaders` (global, prepended in `bootstrap/app.php`) sends:

- `script-src 'self' 'nonce-…' 'unsafe-eval'` — a per-request nonce from `Vite::useCspNonce()` is applied to Vite tags and to the inline script in `resources/views/components/layouts/base.blade.php` (`nonce="{{ Vite::cspNonce() }}"`). Every new inline script must use that nonce. `'unsafe-eval'` is allowed for Alpine.
- `default-src 'self'`, `object-src 'none'`, `frame-ancestors 'none'`, `base-uri 'self'`, `form-action 'self'`, `img-src 'self' data: blob:`, `style-src 'self' 'unsafe-inline'`.
- The Vite dev server origin (`SECURITY_VITE_DEV_SERVER`, local only) is added to `script-src`/`style-src`/`connect-src`.
- `horizon*`, `telescope*`, `pulse*` and `docs/api*` are excluded from the CSP header; they are gated by `system.tools.view` (Telescope: local only).
- `SECURITY_CSP_REPORT_ONLY=true` switches to `Content-Security-Policy-Report-Only` for safe rollout.

## Consequences
- Injected `<script>` tags without the nonce are blocked; inline event handlers (`onclick=`) do not run.
- `'unsafe-eval'` weakens protection against injection into eval sinks; mitigated by escaped Blade output (`{{ }}`, `{!! !!}` only with a justification comment — arch test) and no third-party scripts.
- Integrations that load external scripts/frames (payment widgets, analytics, Sentry browser SDK) must extend the policy deliberately (see `docs/add-ons/`).

## Alternatives considered
- `@alpinejs/csp` build (no eval) — rejected: it only supports registered `Alpine.data()` components and simple property access, not inline expressions. Agents and developers write inline Alpine expressions constantly; the restriction would cause frequent, confusing runtime failures.
- No CSP — rejected: loses the strongest XSS mitigation and clickjacking protection.
- Applying the CSP to vendor dashboards — rejected: they would break without `'unsafe-inline'`.
