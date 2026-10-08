<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use App\Shared\Navigation\Navigation;
use App\Shared\View\Components\Date;
use App\Shared\View\Composers\LayoutComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/** Cross-cutting UI wiring: navigation registry, layout data, class-based UI kit components. */
final class SharedServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $singletons = [
        Navigation::class => Navigation::class,
    ];

    public function boot(): void
    {
        View::composer(['components.layouts.*', 'errors::*', 'errors.*'], LayoutComposer::class);

        Blade::component(Date::class, 'ui.date');
    }
}
