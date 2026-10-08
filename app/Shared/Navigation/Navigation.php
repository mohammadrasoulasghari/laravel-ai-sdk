<?php

declare(strict_types=1);

namespace App\Shared\Navigation;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Router;

/**
 * Registry modules add menu entries to (in their provider's bootModule()).
 * Layouts render only the items the current user may see.
 */
final class Navigation
{
    /** @var list<NavigationItem> */
    private array $items = [];

    public function __construct(
        private readonly Router $router,
        private readonly Gate $gate,
    ) {}

    public function add(NavigationItem $item): void
    {
        $this->items[] = $item;
    }

    /** @return list<NavigationItem> */
    public function visible(NavigationArea $area, ?Authenticatable $user): array
    {
        $items = array_filter(
            $this->items,
            fn (NavigationItem $item): bool => $item->area === $area
                && $this->router->has($item->route)
                && ($item->ability === null || ($user instanceof Authenticatable && $this->gate->forUser($user)->allows($item->ability))),
        );

        usort($items, static fn (NavigationItem $a, NavigationItem $b): int => $a->order <=> $b->order);

        return $items;
    }
}
