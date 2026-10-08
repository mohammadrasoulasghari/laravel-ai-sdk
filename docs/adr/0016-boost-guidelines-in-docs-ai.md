# ADR-0016: Laravel Boost guidelines live in `docs/ai/laravel-boost.md`

- Status: Accepted
- Date: 2026-10-08

## Context
Laravel Boost (`laravel/boost`, dev) provides an MCP server (`search-docs`, `database-schema`, `list-routes`, `read-log-entries` …) and generates generic, version-aware Laravel guidelines. By default it writes those guidelines into `CLAUDE.md` / `AGENTS.md`, which would bury the project's own rules under hundreds of generic lines and get overwritten on every `boost:update`. Agents follow short, specific instructions far more reliably than long ones.

## Decision
- `config/boost.php` sets `agents.claude_code.guidelines_path` and `agents.opencode.guidelines_path` to `docs/ai/laravel-boost.md`. `boost.json` enables guidelines and MCP; the MCP server is declared in `.mcp.json` (`php artisan boost:mcp`).
- `CLAUDE.md` stays hand-written and short (target: at most ~150 lines): architecture, golden rules, commands, definition of done, "never" list, agent workflow. It links to `docs/ai/laravel-boost.md` and states that **`CLAUDE.md` wins on conflicts**.
- `AGENTS.md` is a pointer to `CLAUDE.md` for other tools.
- Regenerate Boost output with `php artisan boost:update`; never hand-edit `docs/ai/laravel-boost.md`.

## Consequences
- Project rules stay visible and stable; Boost updates cannot clobber them.
- Agents must follow a link to read generic guidance — acceptable, since the MCP `search-docs` tool is the primary source for framework details.
- Conflicts (e.g. Boost suggesting repositories or Fortify) are resolved by the precedence rule, not by editing generated text.

## Alternatives considered
- Let Boost write into `CLAUDE.md` — rejected: file bloats, project rules get diluted and overwritten.
- No Boost — rejected: loses version-correct docs search and schema/route introspection over MCP.
