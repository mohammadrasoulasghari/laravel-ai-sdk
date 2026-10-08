# ADR-0014: Rector applies only current-version Laravel sets

- Status: Accepted
- Date: 2026-10-08

## Context
`LaravelLevelSetList::UP_TO_LARAVEL_130` replays every upgrade since Laravel 5. Old rules misfire on modern code — e.g. the 7.0 rule rewrote `Blade::component(Class, 'alias')` to `aliasComponent()`, which registers a *directive* and broke every view.

## Decision
Rector runs PHP 8.3 sets, prepared quality sets, and only `LaravelSetList::LARAVEL_130` plus the code-quality/collection/type-declaration Laravel sets. When upgrading Laravel, add the next single-version set (e.g. `LARAVEL_140`), run `composer fix`, then remove it.

## Consequences
No historical rule surprises. Upgrades are explicit.
