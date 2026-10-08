#!/usr/bin/env bash
# Stop / SubagentStop: if PHP files changed, the agent may only finish when arch tests and
# PHPStan (on changed app files) pass. Exit 2 = keep working, stderr explains why.
set -uo pipefail
cd "$CLAUDE_PROJECT_DIR"

input="$(cat)"
active="$(printf '%s' "$input" | php .claude/hooks/json-field.php stop_hook_active)"
[ "$active" = "true" ] && exit 0   # already continuing because of this hook: avoid loops

changed="$( { git diff --name-only HEAD 2>/dev/null; git ls-files --others --exclude-standard; } | sort -u | grep -E '\.php$' || true)"
[ -z "$changed" ] && exit 0

if ! arch="$(vendor/bin/pest --testsuite=Arch --compact 2>&1)"; then
  printf 'Architecture tests fail — fix before finishing (fix-failing-check skill):\n%s\n' "$(printf '%s' "$arch" | tail -40)" >&2
  exit 2
fi

app_files=()
while IFS= read -r f; do
  [[ "$f" =~ ^(app|routes)/[A-Za-z0-9_./-]+\.php$ && -f "$f" ]] && app_files+=("$f")
done <<< "$changed"
if [ "${#app_files[@]}" -gt 0 ]; then
  if ! stan="$(vendor/bin/phpstan analyse --no-progress --memory-limit=1G --error-format=raw -- "${app_files[@]}" 2>&1)"; then
    printf 'PHPStan fails on changed files — fix before finishing:\n%s\n' "$(printf '%s' "$stan" | tail -40)" >&2
    exit 2
  fi
fi

exit 0
