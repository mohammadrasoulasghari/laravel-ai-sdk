# ADR-0017: Project generators (`make:*`) and `starter:init`

- Status: Accepted
- Date: 2026-10-08

## Context
Laravel's built-in `make:*` commands write into `app/Http`, `app/Models` … and know nothing about modules, DTOs, Query objects, the "no `Action` suffix" rule or the paired tests we require. Hand-copying Blog files invites drift from the conventions, and agents produce more consistent code when the skeleton is generated. A fresh clone of the kit also needs renaming (package, app name, URL, git history) before it becomes a real project.

## Decision
- Custom commands in `app/Console/Commands`: `make:module`, `make:action` (Action + DTO + feature test, rejects an `Action` suffix), `make:dto`, `make:query`, `make:value-object` (+ unit test), `make:ui-component`.
- Templates are plain stubs in `stubs/starter/*.stub` with `{{ placeholder }}` replacements. Editing a stub changes what every future generation produces.
- Commands **compose** the `App\Console\Support\StubRenderer` service (render, write-if-absent — never overwrite — and format written files) and the `ModuleName` value helper, instead of extending Laravel's `GeneratorCommand`. They stay `final`, small and testable (`tests/Feature/Console/GeneratorsTest.php`).
- `make:module` registers the provider in `bootstrap/providers.php` after the `// modules` marker.
- `starter:init` (`--name`, `--package`, `--url`, `--remove-example`, `--fresh-git`, `--dry-run`, `--path`) rewrites `composer.json`, `package.json`, `.env.example`/`.env` (`APP_NAME`, `APP_URL`), the README title and `CHANGELOG.md`, optionally deletes the Blog module and its tests (and its provider/seeder lines), generates an app key, and optionally re-initialises git with one initial commit. Interactive by default, scriptable with `--no-interaction`. Tested in `tests/Feature/Console/StarterInitCommandTest.php`.

## Consequences
- New code starts convention-compliant; skills tell agents to generate first, then fill in.
- Stubs must be kept in sync with conventions; `GeneratorsTest` asserts the generated content.
- Laravel's own `make:model`, `make:migration --path=…`, `make:policy` are still used where no custom generator exists.

## Alternatives considered
- Extend `GeneratorCommand` — rejected: its path/namespace logic assumes `app/` root classes; overriding it is more code than composing a renderer.
- A modules package (e.g. nwidart/laravel-modules) — rejected in ADR-0004; its generators follow a different structure.
- Template repository only, no `starter:init` — rejected: renaming by hand is error-prone and leaves the kit's history and example module behind.
