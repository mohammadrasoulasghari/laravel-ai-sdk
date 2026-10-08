# ADR-0013: Static analysis scope and strictness

- Status: Accepted
- Date: 2026-10-08

## Context
PHPStan level `max` flags every `env()`/`config()` read as `mixed`. Published vendor files (`config/*.php`, framework/package migrations in `database/migrations`) would need hundreds of casts we do not own.

## Decision
- Level **max**, no baseline, `@phpstan-ignore` forbidden without an inline justification reviewed in PR.
- Analysed: `app/` (including every module's migrations, factories and seeders), `bootstrap/app.php`, `database/factories`, `database/seeders`, `routes/`.
- Not analysed: `config/` and the root `database/migrations` (vendor-published). Typed access to config in app code goes through `Config::string()/integer()/boolean()/array()` which Larastan understands.
- Type coverage 100% via Pest, which covers the excluded files' absence of logic.

## Consequences
New migrations belong in modules (`app/Modules/*/database/migrations`) and are analysed.
