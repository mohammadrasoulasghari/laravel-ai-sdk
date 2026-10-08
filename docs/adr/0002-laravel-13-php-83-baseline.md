# ADR-0002: Laravel 13 on PHP 8.3 baseline

- Status: Accepted
- Date: 2026-10-08

## Context
Laravel 13.35 (latest major) requires PHP ^8.3. PHP 8.4/8.5 exist, but many hosts and the build machine run 8.3.

## Decision
Target `php ^8.3` and `laravel/framework ^13`. Sail/Docker images use PHP 8.4 runtime; CI tests on 8.3 and 8.4 so the floor stays honest.

## Consequences
Packages that require PHP 8.4 (Pest 5 family) cannot be used yet — see ADR-0003. Raising the floor later is a one-line change plus `composer update`.

## Alternatives considered
- PHP 8.4 floor — excludes common hosting and the current dev machine.
