<?php

declare(strict_types=1);

use App\Shared\Auth\SystemAbility;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use Illuminate\Support\Facades\Route;

/** Registered navigation items via reflection (Navigation exposes only visible()). */
function registeredItems(): array
{
    $items = (new ReflectionProperty(Navigation::class, 'items'))->getValue(resolve(Navigation::class));

    return is_array($items) ? $items : [];
}

it('registers the admin entry in the main navigation behind admin.access', function (): void {
    $item = collect(registeredItems())->first(fn (NavigationItem $item): bool => $item->area === NavigationArea::App && $item->route === 'admin.dashboard');

    expect($item)->toBeInstanceOf(NavigationItem::class)
        ->and($item?->ability)->toBe(SystemAbility::AccessAdmin->value)
        ->and($item?->label)->toBe('admin::admin.nav')
        ->and($item?->activePattern())->toBe('admin.*');
});

it('registers admin sections in order', function (): void {
    $routes = collect(registeredItems())
        ->filter(fn (NavigationItem $item): bool => $item->area === NavigationArea::Admin)
        ->sortBy('order')
        ->map(fn (NavigationItem $item): string => $item->route)
        ->values()
        ->all();

    expect($routes[0])->toBe('admin.dashboard')
        ->and($routes)->toContain('admin.users.index', 'admin.roles.index', 'admin.settings.edit')
        ->and(array_search('admin.users.index', $routes, true))->toBeLessThan(array_search('admin.roles.index', $routes, true))
        ->and(end($routes))->toBe('admin.settings.edit');
});

it('shows guests no admin navigation', function (): void {
    expect(resolve(Navigation::class)->visible(NavigationArea::Admin, null))->toBe([]);
});

it('puts every admin route behind the admin middleware group', function (): void {
    $adminRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => str_starts_with((string) $route->getName(), 'admin.'));

    expect($adminRoutes)->not->toBeEmpty();

    $adminRoutes->each(function (Illuminate\Routing\Route $route): void {
        expect($route->gatherMiddleware())->toContain('admin')
            ->and($route->uri())->toStartWith('admin');
    });
});

it('translates the admin labels', function (string $locale): void {
    foreach (['admin::admin.nav', 'admin::admin.dashboard', 'admin::admin.intro'] as $key) {
        expect(__($key, locale: $locale))->not->toBe($key);
    }
})->with(['en', 'fa']);
