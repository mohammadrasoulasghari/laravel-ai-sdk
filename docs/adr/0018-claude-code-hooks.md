# ADR-0018: Claude Code hooks enforce rules during agent work

- Status: Accepted
- Date: 2026-10-08

## Context
Instructions in `CLAUDE.md` are advice; agents sometimes ignore them, edit secrets, or stop with a broken build. Claude Code hooks run deterministic scripts around tool calls, so the most important rules can be enforced while the agent works rather than discovered in CI. Hooks must be fast — slow hooks make every edit and every stop painful — and must not trap the agent in a loop.

## Decision
Hooks are configured in `.claude/settings.json`, scripts in `.claude/hooks/` (JSON input parsed by `json-field.php`):

| Event | Script | Enforces |
|---|---|---|
| `PreToolUse` (Edit/Write/MultiEdit/NotebookEdit) | `guard-protected-files.sh` | Blocks (exit 2) edits to `.env*` (except `.env.example`), `vendor/`, `node_modules/`, and migrations already committed to git (`database/migrations`, `app/Modules/*/Database/Migrations`). |
| `PostToolUse` (Edit/Write/MultiEdit) | `pint-edited-file.sh` | Formats the edited PHP file with Pint. |
| `Stop`, `SubagentStop` | `verify-on-stop.sh` | If PHP files changed (vs `HEAD`, including untracked): the `Arch` test suite and PHPStan on changed `app/`/`routes/` files must pass, otherwise exit 2 with the errors so the agent keeps working. |

- **Loop guard:** `verify-on-stop.sh` exits 0 when the input has `stop_hook_active: true` — the agent is already continuing because of this hook, so it gets one forced retry, not an endless loop.
- **Only arch + PHPStan on stop:** both are fast and catch the violations agents make most (boundaries, Eloquent in controllers, Facades in domain code, types). The full gate — Rector, Prettier, the whole Pest suite with coverage, type coverage, audits, mutation — runs in `composer check` (the `qa-verifier` agent) and in CI (`.github/workflows/ci.yml`).
- `permissions.deny` additionally blocks reading `.env` and destructive commands.

## Consequences
- Agents cannot finish a turn with broken architecture or static analysis on the files they touched; formatting noise disappears from reviews.
- Stop can take tens of seconds on large change sets (timeout 300 s).
- Hooks need `php` and `vendor/` installed locally; humans are covered by CaptainHook git hooks (ADR-0012) and CI.

## Alternatives considered
- Run `composer check` on every stop — rejected: minutes per stop, agents would time out or loop.
- Rely on CLAUDE.md instructions only — rejected: not enforced.
- No loop guard — rejected: a persistent failure would block the agent forever.
