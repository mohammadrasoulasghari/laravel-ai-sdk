# AGENTS.md

Instructions for AI coding agents (Cursor, Copilot, Codex, OpenCode, Gemini, …).

**The single source of truth is [`CLAUDE.md`](CLAUDE.md).** Read it first and follow it exactly —
architecture, golden rules, commands, definition of done and the list of things you must never do.

Then, depending on the task:

- Conventions with good/bad examples: [`docs/conventions.md`](docs/conventions.md)
- Architecture and module boundaries: [`docs/architecture.md`](docs/architecture.md), [`docs/adr/`](docs/adr)
- Step-by-step recipes (they are tool-agnostic Markdown): [`.claude/skills/*/SKILL.md`](.claude/skills)
- Role descriptions you can adopt (planner, builder, reviewer, …): [`.claude/agents/*.md`](.claude/agents)
- Generic Laravel guidance (Laravel Boost): [`docs/ai/laravel-boost.md`](docs/ai/laravel-boost.md) — `CLAUDE.md` wins on conflicts.

Before you say you are done, run:

```bash
composer check
```

It must pass. Do not weaken rules, skip tests or add PHPStan ignores to make it pass.
