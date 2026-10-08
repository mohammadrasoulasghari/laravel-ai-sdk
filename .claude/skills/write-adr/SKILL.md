---
name: write-adr
description: Record an architecture decision record (ADR) in docs/adr using the project template. Use whenever you choose between non-obvious alternatives, add a dependency, or deviate from a convention.
---

# Write an ADR

1. Next number: `ls docs/adr | sort | tail -3` → increment (4 digits).
2. File: `docs/adr/NNNN-kebab-title.md` from `docs/adr/template.md`.
3. Fill:
   - **Context**: forces, constraints, versions (verified, not from memory).
   - **Decision**: active voice, specific ("We use X for Y; Z is forbidden").
   - **Consequences**: what gets easier/harder, follow-ups, how it is enforced (arch test, PHPStan rule, hook).
   - **Alternatives considered**: each with the reason it lost.
4. Status `Accepted` (or `Proposed` if a human must decide). Superseding? Set old ADR to `Superseded by ADR-NNNN` — the only edit allowed to an accepted ADR.
5. If it changes a rule, update `docs/conventions.md` (and the enforcer) in the same commit.
6. Commit: `docs(adr): record <decision>`.

Keep it under one page. Example: `docs/adr/0005-no-repository-pattern.md`.
