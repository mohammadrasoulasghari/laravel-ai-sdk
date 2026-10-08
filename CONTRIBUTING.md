# Contributing

Thanks for helping improve the Laravel AI-First Starter Kit. The same rules apply to humans and AI
agents: [`CLAUDE.md`](CLAUDE.md) is the source of truth, [`docs/conventions.md`](docs/conventions.md)
has the details and examples.

## Setup

```bash
make setup          # Docker (Sail); or see README "Without Docker"
composer check      # must be green before you start
```

`composer install` installs the git hooks (CaptainHook): `pre-commit` runs PHP lint, Pint and PHPStan
on staged files; `commit-msg` checks the commit message format.

## Branches

Branch from `main`, named `<type>/<short-kebab-description>`, using the commit types below:

```
feat/blog-post-scheduling
fix/settings-cache-invalidation
docs/payments-add-on
```

## Commits

[Conventional Commits](https://www.conventionalcommits.org), lowercase summary, max 100 characters:

```
feat(blog): add post scheduling
fix(auth)!: require verified email for api tokens
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.
Scope is optional (lowercase module or area). The `commit-msg` hook enforces the regex in
[`captainhook.json`](captainhook.json):

```
^(Merge .+|Revert .+|(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)(\([a-z0-9\-/]+\))?!?: [^A-Z].{1,99})
```

## Definition of done

Use the checklist in [CLAUDE.md → Definition of done](CLAUDE.md#definition-of-done). In short:
`composer check` green, tests for every Action and route, fa + en translations checked in RTL and
LTR, docs and `CHANGELOG.md` `[Unreleased]` updated, Conventional Commit.

Never weaken a gate to get green (no `@phpstan-ignore`, baseline entries or skipped tests) — fix the
cause ([`fix-failing-check`](.claude/skills/fix-failing-check/SKILL.md)).

## Pull requests

1. Keep PRs focused: one feature or fix. Follow the recipes in [`docs/workflows/`](docs/workflows).
2. Fill in the PR template (summary, type, checklist; fa **and** en screenshots for UI changes).
3. CI must pass: `check` on PHP 8.3 and 8.4 (lint, analyse, tests with ≥ 80% coverage on MySQL, type coverage), `security` (composer/npm audit), and `mutation` on PRs.
4. At least one review. With Claude Code, `/review` runs `code-reviewer` and `security-auditor` in parallel on your diff.
5. Squash-merge with a Conventional Commit title.

New dependencies need a reason in the PR, a version check (the `researcher` agent) and usually an ADR.

## Architecture decisions (ADRs)

Any non-obvious choice — a new dependency, a deviation from a convention, choosing between real
alternatives — gets an ADR in [`docs/adr/`](docs/adr):

1. Copy [`docs/adr/template.md`](docs/adr/template.md) to the next number: `docs/adr/NNNN-short-title.md` (or run `/adr <topic>`, skill [`write-adr`](.claude/skills/write-adr/SKILL.md)).
2. Fill in Context, Decision, Consequences, Alternatives considered. Status `Proposed` in the PR, `Accepted` when merged.
3. Never rewrite an accepted ADR's decision; write a new one and mark the old one `Superseded by ADR-XXXX`.

## Security

Do not open public issues for vulnerabilities; contact the maintainers privately (see
[`.github/ISSUE_TEMPLATE/config.yml`](.github/ISSUE_TEMPLATE/config.yml)).
