# ADR-0012: Test database strategy and git hooks

- Status: Accepted
- Date: 2026-10-08

## Context
MySQL is the runtime database. Fast local feedback matters for agents running `composer check` many times per task.

## Decision
- `phpunit.xml` defaults to SQLite in-memory for speed. CI runs the full suite against MySQL 8.4 (service container) by overriding `DB_CONNECTION`. Code must stay portable (no MySQL-only SQL outside migrations; the Query layer uses the builder).
- Git hooks via `captainhook/captainhook` (PHP-native, auto-installed by Composer): pre-commit runs Pint + PHPStan on staged PHP files; commit-msg enforces Conventional Commits with a regex.

## Consequences
Rare SQLite/MySQL divergences surface in CI rather than locally. No Node needed for hooks.

## Alternatives considered
- Husky + commitlint: requires `npm install` before hooks work; PHP-only contributors would skip it.
