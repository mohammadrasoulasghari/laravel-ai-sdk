# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-10-08

### Security
- Final audit (code review + security audit) findings closed before release: super-admin escalation, token expiry, production admin seeding, trusted proxies, secure cookies, Markdown link injection in mail, generator/`starter:init` input validation, `Retry-After` on problem+json, hardened Claude Code hooks and permissions. See `docs/FINAL_REPORT.md`.

### Added

#### Foundation
- Laravel 13 skeleton on PHP 8.3+ (ADR-0002).
- Docker stack via Sail (`compose.yaml`): app, MySQL 8.4, Redis, Mailpit, Horizon worker and scheduler containers.
- Fully commented `.env.example` with MySQL + Redis defaults.
- Composer scripts `setup`, `dev`, `lint`, `analyse`, `test`, `test:types`, `test:coverage`, `test:mutate`, `fix`, `check`, and a `Makefile` wrapping Sail (`setup`, `up`, `down`, `fresh`, `test`, `check`, `fix`, `shell`).

#### Quality tooling
- Pest 4 with arch, Laravel and type-coverage plugins (ADR-0003); 100% type coverage required.
- Larastan/PHPStan at level max without a baseline, plus the custom rule `NoEloquentInControllersRule` (ADR-0013).
- Strict Pint preset, Rector with PHP 8.3 and current-version Laravel sets only (ADR-0014), Prettier with Blade and Tailwind plugins.
- CaptainHook git hooks: `pre-commit` (PHP lint, Pint, PHPStan on staged files) and Conventional Commits `commit-msg` check (ADR-0012).
- GitHub Actions CI: `check` on PHP 8.3 and 8.4 against MySQL (lint, analyse, tests with ≥ 80% coverage, type coverage), `security` (`composer audit`, `npm audit`), `mutation` on pull requests.

#### Architecture
- Modules in `app/Modules` with the `ModuleServiceProvider` base that wires routes, views, translations, migrations, policies, listeners, bindings, navigation and permissions (ADR-0004).
- Actions + Query objects + immutable DTOs over Eloquent, no repositories (ADR-0005); module boundaries with a public API of `Contracts`, `Data`, `Enums`, `Events` (ADR-0006).
- ULID primary keys (ADR-0007); `Model::shouldBeStrict()` outside production.
- `App\Shared`: `DomainException`, RFC 9457 problem+json responses for the API (ADR-0010), typed flash messages, navigation and permission registries, locales/direction/calendar, date formatter.
- Architecture tests: global rules, module conventions, module boundaries, models, Blade and translation parity.

#### Front-end
- Blade + Alpine.js + Tailwind CSS 4 via Vite (ADR-0009): design tokens, dark mode, Vazirmatn font, RTL (fa, default) and LTR (en).
- UI kit of 20 `<x-ui.*>` components, base/app/guest/admin/error layouts, locale switcher and theme toggle.
- Jalali dates for fa via `hekmatinasser/verta` (ADR-0011) and the `<x-ui.date>` component.
- Local-only style guide at `/_styleguide`; error pages 403, 404, 419, 429, 500, 503.

#### Modules
- **Auth** — login with brute-force protection, registration, password reset, email verification, Sanctum API tokens (`/api/v1/auth/tokens`), built on framework primitives (ADR-0008).
- **Users** — admin CRUD and `GET /api/v1/me`; `UserRegistrar` contract.
- **Roles** — roles and permissions on spatie/laravel-permission, role-permission admin screen, `php artisan permissions:sync`.
- **Settings** — typed setting keys with a cached store and admin screen.
- **Admin** — admin dashboard behind the `admin` middleware group.
- **Blog** (reference module) — posts and categories with web and API controllers, policies, slug value object, publish/unpublish with `PostPublished` event and queued author notification, filtering/sorting/pagination, and a Pennant `ReadingTime` feature flag.

#### Cross-cutting
- Security headers and Content Security Policy with per-request nonces (ADR-0015), CORS config, `api` and `auth` rate limiters, encrypted sessions.
- JSON logging with correlation id (`X-Request-Id`) propagated to queued jobs; readiness probe `/health/ready` next to `/up`; error-tracker hook (`ERROR_REPORTING_DSN`).
- Horizon, Pulse and Scramble API docs (`/docs/api`) gated by `system.tools.view`; Telescope in local only.

#### Generators
- `make:module`, `make:action`, `make:dto`, `make:query`, `make:value-object`, `make:ui-component` using stubs in `stubs/starter` (ADR-0017).
- `starter:init` to rename the kit into a new project (`--name`, `--package`, `--url`, `--remove-example`, `--fresh-git`, `--dry-run`).

#### AI-first layer
- `CLAUDE.md` (source of truth) and `AGENTS.md`; Laravel Boost MCP with guidelines in `docs/ai/laravel-boost.md` (ADR-0016).
- Claude Code subagents: researcher, architect, laravel-builder, blade-ui-builder, test-writer, code-reviewer, security-auditor, qa-verifier, docs-keeper.
- Skills: new-module, new-action, web-crud, api-endpoint, blade-component, migration-model, events-jobs, i18n-rtl, writing-tests, write-adr, fix-failing-check.
- Slash commands: `/feature`, `/new-module`, `/review`, `/check`, `/adr`, `/audit`.
- Hooks: protected-file guard, Pint after edits, arch tests + PHPStan on stop (ADR-0018).

#### Documentation
- Architecture, conventions, API contract, caching and observability guides; ADR-0001 to ADR-0018.
- Workflows (`docs/workflows`) and documented add-ons (`docs/add-ons`): multi-tenancy, payments, SMS, two-factor auth, error tracking, media uploads, search.
- README, CONTRIBUTING, pull request and issue templates, MIT license.
