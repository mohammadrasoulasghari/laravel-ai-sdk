# Build Plan — Laravel AI-First Starter Kit

Living roadmap. Tick items as they land. If context resets, resume from the first unchecked item.
Definition of phase-done: `composer check` green → Conventional Commit → one line in `docs/PROGRESS.md`.

## Verified baseline (2026-10-08)

| Item | Version | Note |
|---|---|---|
| PHP | 8.3 (local 8.3.30) | Laravel 13 minimum |
| laravel/framework | 13.35 | latest major |
| Pest | 4.7 | Pest 5 needs PHP 8.4 → ADR-0003 |
| Larastan / PHPStan | 3.13 / 2.3 | level max |
| Tailwind / Vite | 4.3 / 8.3 | `@tailwindcss/vite` |

## Phases

### 1. Research & plan
- [x] Version research (researcher subagent)
- [x] PLAN.md
- [x] Initial ADRs (0001–0012)

### 2. Foundation
- [x] Sail / docker-compose (app, MySQL, Redis, Mailpit)
- [x] `.env.example` fully commented; MySQL + Redis defaults
- [x] Composer scripts (`lint`, `analyse`, `test`, `fix`, `check`) + Makefile

### 3. Quality tooling
- [x] Pest 4 + arch + type-coverage + laravel plugins
- [x] Larastan level max
- [x] Pint strict preset
- [x] Rector (PHP 8.3 + Laravel sets)
- [x] Prettier (+ blade, tailwind plugins)
- [x] CaptainHook (pre-commit, commit-msg)
- [x] GitHub Actions CI (check + audits, MySQL service)

### 4. Architecture skeleton
- [x] `app/Modules` loader (`ModuleServiceProvider` base)
- [x] `app/Shared` base classes (Action contract, Data, ValueObject, DomainException)
- [x] Strict model mode, exception handling, RFC 9457 problem details
- [x] Arch tests enforcing boundaries

### 5. Frontend foundation
- [x] Tailwind tokens, dark mode, Vazirmatn, RTL/LTR
- [x] UI kit components + layouts (app, guest, admin)
- [x] Style Guide page (local only)
- [x] Error pages 403/404/419/429/500/503

### 6. Core modules
- [ ] Auth (login, register, password reset, email verification)
- [ ] Users, Roles & permissions (spatie), Settings, Admin panel

### 7. Reference module
- [ ] Blog (Post, Category): web + API + policy + events + tests

### 8. Generators
- [ ] make:module, make:action, make:dto, make:query, make:value-object, make:ui-component
- [ ] Smoke test: generate a module, check passes, delete

### 9. Cross-cutting
- [ ] Security headers, CORS, secure cookies, rate limits
- [ ] Structured logging + correlation id, health/readiness, error reporting hook
- [ ] Horizon, Telescope (local), Pulse, Pennant, Scramble, Jalali dates

### 10. AI-First layer
- [ ] CLAUDE.md, AGENTS.md, Boost + .mcp.json
- [ ] Subagents, skills, commands, hooks, settings

### 11. Docs & bootstrapper
- [ ] docs/architecture.md, conventions.md, workflows/, add-ons/
- [ ] README, CONTRIBUTING, CHANGELOG, .github templates
- [ ] `php artisan starter:init`

### 12. Agent trial
- [ ] Fresh subagent builds Contact module via /feature; fix docs; delete module

### 13. Final audit
- [ ] code-reviewer + security-auditor over whole codebase; fix findings
- [ ] docs/FINAL_REPORT.md (Persian)
