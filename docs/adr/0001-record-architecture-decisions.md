# ADR-0001: Record architecture decisions

- Status: Accepted
- Date: 2026-10-08

## Context
This kit is developed largely by AI agents. Agents (and humans) re-derive decisions they cannot see, which causes drift.

## Decision
Every non-obvious decision is recorded as a Markdown ADR in `docs/adr/NNNN-kebab-title.md` using `template.md`. ADRs are immutable once accepted; changes are made by a new ADR that supersedes the old one. Use the `write-adr` skill or `/adr <topic>`.

## Consequences
Agents can read the rationale before changing direction. Small cost per decision.

## Alternatives considered
- Wiki / issue tracker — not versioned with code, invisible to agents.
