<?php

declare(strict_types=1);

namespace App\Shared\Navigation;

final readonly class NavigationItem
{
    /**
     * @param  string  $label  Translation key, e.g. "blog::posts.nav".
     * @param  string  $route  Route name the item links to.
     * @param  string|null  $ability  Gate ability (or permission) required to see the item.
     * @param  string|null  $active  Route name pattern that marks the item active (defaults to "<route>*").
     */
    public function __construct(
        public NavigationArea $area,
        public string $label,
        public string $route,
        public ?string $ability = null,
        public ?string $active = null,
        public int $order = 100,
    ) {}

    public function activePattern(): string
    {
        return $this->active ?? $this->route.'*';
    }
}
