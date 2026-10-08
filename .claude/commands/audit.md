---
description: Full-codebase audit — conventions, architecture, security and quality gates
---

1. In parallel delegate:
   - `code-reviewer`: "Audit the entire codebase (app/, routes/, resources/views, tests/) against docs/conventions.md and ADRs. Not a diff review."
   - `security-auditor`: "Audit the entire application for OWASP Top 10 risks, authorization gaps and data exposure, and run composer audit / npm audit."
   - `qa-verifier`: "Run the full verification."
2. Consolidate into `docs/audits/<yyyy-mm-dd>.md`: summary, findings table (severity, area, file:line, fix), gate results.
3. Ask whether to fix blockers now; if the session is autonomous, fix blockers and majors, then re-run `qa-verifier`.
