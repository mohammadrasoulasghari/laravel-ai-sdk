#!/usr/bin/env bash
# PostToolUse (Edit|Write|MultiEdit): format the edited PHP file with Pint.
set -uo pipefail
cd "$CLAUDE_PROJECT_DIR"

file="$(php .claude/hooks/json-field.php tool_input.file_path)"
[[ "$file" == *.php && -f "$file" && "$file" != *"/vendor/"* ]] || exit 0

if ! output="$(vendor/bin/pint --quiet "$file" 2>&1)"; then
  echo "Pint could not format $file: $output" >&2
  exit 1
fi
exit 0
