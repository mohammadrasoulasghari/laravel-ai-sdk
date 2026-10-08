---
description: Build a feature end-to-end with the agent team (plan → build → test → review → verify → document)
argument-hint: <feature description>
---

# /feature — $ARGUMENTS

Run this pipeline. Do not skip steps; do not mark the feature done unless step 6 reports PASS.

1. **Plan** — delegate to the `architect` subagent: "Plan this feature: $ARGUMENTS". It writes `docs/plans/<date>-<slug>.md` (and ADRs if needed). Read the plan.
2. **Build** — delegate to `laravel-builder` with the plan path (back-end). If the plan has UI, delegate the views to `blade-ui-builder` (can run in parallel once routes/controllers exist). Builders use the skills in `.claude/skills/`.
3. **Test** — delegate to `test-writer` with the plan path and the list of changed files.
4. **Review** — run `code-reviewer` and `security-auditor` **in parallel** on `git diff` (include untracked files: `git add -N .` first).
5. **Fix** — if either reviewer requests changes or fails, send the findings back to the relevant builder, then repeat steps 3–4 for the changed files.
6. **Verify** — delegate to `qa-verifier`. On FAIL, send the exact errors to the builder (`fix-failing-check` skill) and repeat from step 3. Loop at most 3 times; then stop and report what is blocking.
7. **Document** — delegate to `docs-keeper` (CHANGELOG, docs, skills corrections).
8. **Commit** — `git add -A && git commit -m "feat(<module>): <summary>"` (Conventional Commits; hooks run Pint + PHPStan).

Finish with a short report: what was built, files touched, test count, and any open follow-ups.
