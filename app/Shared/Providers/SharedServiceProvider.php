<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use App\Shared\Auth\PermissionRegistry;
use App\Shared\Auth\SystemAbility;
use App\Shared\Navigation\Navigation;
use App\Shared\View\Components\Date;
use App\Shared\View\Composers\LayoutComposer;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecuritySchemes\HttpSecurityScheme;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Cross-cutting wiring: navigation registry, layout data, class-based UI kit components,
 * system permissions, tool dashboards access, log context and API docs.
 */
final class SharedServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $singletons = [
        Navigation::class => Navigation::class,
        PermissionRegistry::class => PermissionRegistry::class,
    ];

    public function boot(): void
    {
        View::composer(['components.layouts.*', 'errors::*', 'errors.*'], LayoutComposer::class);

        Blade::component(Date::class, 'ui.date');

        $this->app->make(PermissionRegistry::class)->register(SystemAbility::cases(), defaultRoles: ['admin']);

        // Pulse, Scramble (and Horizon/Telescope providers) ask these gates outside local.
        foreach (['viewPulse', 'viewApiDocs'] as $ability) {
            Gate::define($ability, static fn (?Authenticatable $user = null): bool => $user instanceof Authenticatable
                && Gate::forUser($user)->allows(SystemAbility::ViewSystemTools->value));
        }

        // Every log line and queued job after authentication carries the user id.
        Event::listen(Authenticated::class, static function (Authenticated $event): void {
            Context::add('user_id', $event->user->getAuthIdentifier());
        });

        Scramble::configure()->withDocumentTransformers(static function (OpenApi $openApi): void {
            $openApi->secure((new HttpSecurityScheme('bearer'))->as('http'));
        });
    }
}
