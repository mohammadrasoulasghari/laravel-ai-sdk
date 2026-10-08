<?php

declare(strict_types=1);

namespace Tests;

use App\Modules\Users\Models\User;
use Symfony\Component\Finder\Finder;

/**
 * Names of all modules in app/Modules (directory names).
 *
 * @return list<string>
 */
function modules(): array
{
    $dirs = glob(root().'/app/Modules/*', GLOB_ONLYDIR);

    return array_values(array_map(basename(...), $dirs === false ? [] : $dirs));
}

/**
 * Every Blade view we own (app views + module views), keyed by relative path.
 *
 * @return array<string, array{string, string}>
 */
function bladeFiles(): array
{
    $root = root();
    $finder = Finder::create()
        ->files()
        ->name('*.blade.php')
        ->in([$root.'/resources/views', ...glob($root.'/app/Modules/*/resources/views', GLOB_ONLYDIR) ?: []])
        ->exclude(['vendor']);

    $files = [];

    foreach ($finder as $file) {
        $relative = mb_substr($file->getPathname(), mb_strlen($root) + 1);
        $files[$relative] = [$relative, $file->getContents()];
    }

    ksort($files);

    return $files;
}

/** Absolute project root, usable before the Laravel app boots (datasets, arch tests). */
function root(string $path = ''): string
{
    return dirname(__DIR__, 2).($path === '' ? '' : '/'.mb_ltrim($path, '/'));
}

/**
 * A verified user holding the given role (see App\Modules\Roles\Enums\Role).
 */
function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

/**
 * A verified user holding exactly the given permissions (plus nothing else).
 */
function userWithPermissions(string ...$permissions): User
{
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);

    return $user;
}
