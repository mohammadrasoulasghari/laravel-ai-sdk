#!/usr/bin/env bash
# PreToolUse (Edit|Write|MultiEdit|NotebookEdit): block edits to secrets, dependencies and
# migrations that are already committed (write a new migration instead). Exit 2 = block.
set -euo pipefail
cd "$CLAUDE_PROJECT_DIR"

file="$(php .claude/hooks/json-field.php tool_input.file_path)"
[ -z "$file" ] && exit 0
# Normalise ./ and ../ so "config/../.env" cannot slip past the checks.
abs="$(realpath -m -- "$file")"
root="$(realpath -m -- "$CLAUDE_PROJECT_DIR")"
rel="${abs#"$root"/}"

case "$rel" in
  .env|.env.*)
    [ "$rel" = ".env.example" ] && exit 0
    echo "Blocked: never edit $rel (secrets). Change .env.example and config/*.php instead." >&2; exit 2 ;;
  vendor/*|node_modules/*)
    echo "Blocked: $rel is a dependency. Use composer/npm, or extend in app/." >&2; exit 2 ;;
esac

if [[ "$rel" == database/migrations/*.php || "$rel" == app/Modules/*/Database/Migrations/*.php ]]; then
  if git ls-files --error-unmatch "$rel" >/dev/null 2>&1 && git cat-file -e "HEAD:$rel" 2>/dev/null; then
    echo "Blocked: $rel is already committed. Create a new migration (migration-model skill)." >&2; exit 2
  fi
fi

exit 0
