# Two-factor authentication

## When to use

Admin accounts (always a good idea) or all users need a second factor (TOTP authenticator app, with recovery codes).

## Recommended package

**`pragmarx/google2fa`** for TOTP secrets/verification and **`bacon/bacon-qr-code`** for the QR code. The core Auth module is hand-rolled on framework primitives (ADR-0008), so Fortify is not used; adding these small libraries fits that decision. Verify current versions with the `researcher` agent.

## Module to change

Extend `app/Modules/Auth` (it owns login) — or create `php artisan make:module TwoFactor` if you want it removable. Store secrets on the user via a new migration in the Users module (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`), with `encrypted` casts.

## Integration points

- **Actions** — `EnableTwoFactor` (generate secret + recovery codes), `ConfirmTwoFactor` (verify first code), `DisableTwoFactor` (requires password), `VerifyTwoFactorChallenge`.
- **Login flow** — `App\Modules\Auth\Actions\AuthenticateUser` currently logs the user in directly. When 2FA is confirmed, store the pending user id in the session instead and redirect to a challenge page; complete login only after `VerifyTwoFactorChallenge`.
- **API tokens** — `IssueApiToken` must require a `code` field when 2FA is enabled.
- **Admin enforcement** — optional middleware in the `admin` group (`bootstrap/app.php`) that redirects admins without 2FA to the setup page.
- **Rate limiting** — reuse the `auth` limiter for the challenge route.
- **UI** — setup page with QR (the SVG is generated server-side; render with `{!! !!}` plus a `{{-- unescaped: … --}}` comment), recovery codes shown once; translations in fa and en.

## Tests to add

- Enable → confirm with a valid code; invalid code rejected.
- Login with 2FA enabled redirects to the challenge; wrong code fails; recovery code works once.
- Token issuing without/with wrong `code` → 422 problem+json.
- Disabling requires the current password.
- Secrets are encrypted at rest (read the raw column).
