# ADR-0008: Auth module built on framework primitives (no Fortify/Breeze)

- Status: Accepted
- Date: 2026-10-08

## Context
Breeze/Jetstream scaffold code into `app/` with their own conventions (no Actions/DTO split, no `final`, Livewire/Inertia variants). Fortify is headless but places actions in `app/Actions/Fortify` and controls routes through config, which fights our module structure and arch rules.

## Decision
The `Auth` module implements login, register, logout, password reset and email verification with Laravel primitives (`Auth`, `Password` broker, `MustVerifyEmail`, `RateLimiter`) using our Action + FormRequest + Blade UI kit conventions. API authentication uses Sanctum personal access tokens.

## Consequences
~15 small classes we own and test. No hidden behaviour. 2FA is documented as an add-on.

## Alternatives considered
- Fortify: viable, but less consistent with conventions agents must copy.
