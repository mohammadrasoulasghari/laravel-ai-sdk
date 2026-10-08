# ADR-0004: Modules live in `app/Modules`

- Status: Accepted
- Date: 2026-10-08

## Context
We need domain modules with clear boundaries. Options: `app/Modules/*` under the existing `App\` PSR-4 root, or a top-level `modules/` folder with its own autoload entry.

## Decision
Modules live in `app/Modules/<Name>` with namespace `App\Modules\<Name>`. Each module registers itself through `Providers/<Name>ServiceProvider` listed in `bootstrap/providers.php` (the `make:module` generator does this).
Cross-cutting code lives in `app/Shared`.

## Consequences
- Zero extra autoload config; Larastan, Rector, Pint and IDEs pick modules up by default.
- Module boundaries are enforced by arch tests, not by package isolation.

## Alternatives considered
- `modules/` + per-module `composer.json` (nwidart/laravel-modules style): more ceremony, plugin dependency, harder for agents to navigate.
