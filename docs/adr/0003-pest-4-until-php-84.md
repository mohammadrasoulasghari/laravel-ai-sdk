# ADR-0003: Pest 4 until the PHP floor is 8.4

- Status: Accepted
- Date: 2026-10-08

## Context
Pest 5.x and its plugins require PHP ^8.4. Our floor is 8.3 (ADR-0002).

## Decision
Use `pestphp/pest ^4.7` with `pest-plugin-laravel ^4`, `pest-plugin-arch ^4`, `pest-plugin-type-coverage ^4`. Pest's built-in `--mutate` is used for mutation testing instead of Infection to avoid a second test runner configuration.

## Consequences
When the floor becomes 8.4, upgrade with `composer require --dev pestphp/pest:^5 ... -W` and re-run `composer check`.

## Alternatives considered
- PHPUnit only — loses arch testing and type coverage.
- Infection — mature, but duplicates configuration; Pest's mutate is enough for Actions/ValueObjects.
