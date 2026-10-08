<?php

declare(strict_types=1);

use function Tests\bladeFiles;

/*
| Rules for Blade that PHP tooling cannot see. Each is documented in docs/conventions.md.
*/

it('uses only logical (RTL-safe) Tailwind direction utilities', function (string $path, string $contents): void {
    preg_match_all(
        '/(?<![\w:-])(?:[a-z]+:)*(-?(?:ml|mr|pl|pr|left|right|border-l|border-r|rounded-l|rounded-r|rounded-tl|rounded-tr|rounded-bl|rounded-br|text-left|text-right|float-left|float-right|scroll-ml|scroll-mr)(?:-[\w\/.\[\]]+)?)(?![\w-])/',
        strip_comments($contents),
        $matches,
    );

    expect($matches[1])->toBe([], "{$path}: use ms-/me-/ps-/pe-/start-/end-/text-start/text-end instead");
})->with(bladeFiles(...));

it('never prints unescaped output without a documented reason', function (string $path, string $contents): void {
    $lines = explode("\n", $contents);
    $violations = [];

    foreach ($lines as $number => $line) {
        if (str_contains($line, '{!!') && ! str_contains($lines[$number - 1] ?? '', '{{-- unescaped:')) {
            $violations[] = $number + 1;
        }
    }

    expect($violations)->toBe([], "{$path}: add a '{{-- unescaped: <reason> --}}' comment on the line above {!! !!}");
})->with(bladeFiles(...));

it('has no hard-coded user-facing text (use __())', function (string $path, string $contents): void {
    $text = strip_comments($contents);
    $text = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $text) ?? '';
    $text = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', '', $text) ?? '';
    $text = preg_replace('/@(?:php|verbatim)\b.*?@end(?:php|verbatim)/s', '', $text) ?? '';
    $text = preg_replace('/@\w+(?:\s*\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\))?/', '', $text) ?? '';
    $text = preg_replace('/<[^>]*>/s', ' ', $text) ?? '';

    preg_match_all('/[\p{L}][\p{L}\p{M}\'’ ]{1,}/u', $text, $matches);
    $words = array_values(array_filter(array_map(trim(...), $matches[0]), static fn (string $w): bool => $w !== ''));

    expect($words)->toBe([], "{$path}: wrap visible text in __('...')");
})->with(bladeFiles(...));

function strip_comments(string $blade): string
{
    return preg_replace('/\{\{--.*?--\}\}|<!--.*?-->/s', '', $blade) ?? $blade;
}
