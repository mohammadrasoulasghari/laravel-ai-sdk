<?php

declare(strict_types=1);

use App\Modules\Users\Models\User;

use function Tests\modules;

/*
| ADR-0006: a module may only use another module's public API:
| Contracts\, Events\, Data\, Enums\ (+ the shared Users\Models\User identity).
*/
$internal = ['Actions', 'Console', 'Exceptions', 'Http', 'Jobs', 'Listeners', 'Models', 'Policies', 'Providers', 'Queries', 'ValueObjects', 'Database'];

foreach (modules() as $module) {
    $others = array_values(array_filter(modules(), static fn (string $m): bool => $m !== $module));

    if ($others === []) {
        continue;
    }

    $forbidden = [];

    foreach ($others as $other) {
        foreach ($internal as $layer) {
            $forbidden[] = "App\\Modules\\{$other}\\{$layer}";
        }
    }

    arch("{$module} does not reach into other modules' internals")
        ->expect("App\\Modules\\{$module}")
        ->not->toUse($forbidden)
        ->ignoring(User::class);
}
