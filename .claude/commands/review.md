---
description: Review the current changes with code-reviewer and security-auditor in parallel
---

1. Run `git add -N . && git diff HEAD --stat` to see the change set.
2. In parallel, delegate to the `code-reviewer` and `security-auditor` subagents with: "Review the working-tree diff against HEAD (`git diff HEAD`)."
3. Merge their findings into one list ordered by severity, de-duplicated, with file:line and the concrete fix. End with the combined verdict.
