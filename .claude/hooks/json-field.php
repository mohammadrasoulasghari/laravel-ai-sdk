<?php

declare(strict_types=1);

// Usage: echo "$INPUT" | php .claude/hooks/json-field.php tool_input.file_path
// Prints the dotted field of the hook's JSON stdin (empty string when missing).
$data = json_decode((string) stream_get_contents(STDIN), true);
$value = $data;

foreach (explode('.', $argv[1] ?? '') as $key) {
    $value = is_array($value) && array_key_exists($key, $value) ? $value[$key] : '';
}

echo is_scalar($value) ? var_export($value, true) === 'true' ? 'true' : (string) $value : '';
