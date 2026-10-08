<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

use function Tests\root;

/*
| Every translation key exists in every supported locale (fa and en), for the
| app lang/ folder and each module's lang/ folder.
*/
dataset('translation files', function (): array {
    $roots = [root('lang'), ...(glob(root('app/Modules/*/lang'), GLOB_ONLYDIR) ?: [])];
    $cases = [];

    foreach ($roots as $root) {
        foreach (glob($root.'/en/*.php') ?: [] as $file) {
            $cases[str_replace(root().'/', '', $file)] = [$root, basename($file)];
        }
    }

    return $cases;
});

it('has matching keys in fa and en', function (string $root, string $file): void {
    $en = array_keys(Arr::dot(require "{$root}/en/{$file}"));
    $faPath = "{$root}/fa/{$file}";

    expect($faPath)->toBeFile();

    $fa = array_keys(Arr::dot(require $faPath));

    expect(array_values(array_diff($en, $fa)))->toBe([], 'missing in fa')
        ->and(array_values(array_diff($fa, $en)))->toBe([], 'missing in en');
})->with('translation files');
