<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Base provider every module extends. It wires the module's conventional folders:
 *
 *  routes/web.php          → "web" middleware group
 *  routes/api.php          → "api" group, prefixed /api/v1, names prefixed "api.v1."
 *  resources/views         → view namespace "<module-kebab>::"
 *  lang/<locale>/*.php     → translation namespace "<module-kebab>::"
 *  database/migrations     → loaded with the app migrations
 *
 * Subclasses declare $policies, $listen, and Laravel's native $bindings / $singletons.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> Model class => Policy class. */
    protected array $policies = [];

    /** @var array<class-string, list<class-string>> Event class => listener classes. */
    protected array $listen = [];

    public function boot(): void
    {
        $path = $this->modulePath();
        $namespace = $this->moduleNamespace();

        $this->loadMigrationsFrom($path.'/database/migrations');
        $this->loadViewsFrom($path.'/resources/views', $namespace);
        $this->loadTranslationsFrom($path.'/lang', $namespace);

        $this->registerRoutes($path);

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        $this->bootModule();
    }

    /** Kebab-case module key used for view and translation namespaces, e.g. "blog". */
    final public function moduleNamespace(): string
    {
        return Str::kebab($this->moduleName());
    }

    /** Module name derived from the provider namespace App\Modules\<Name>\Providers. */
    final public function moduleName(): string
    {
        return Str::of(static::class)->after('App\\Modules\\')->before('\\')->toString();
    }

    /** Hook for module-specific boot logic (rate limiters, view composers, ...). */
    protected function bootModule(): void {}

    private function registerRoutes(string $path): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        if (is_file($path.'/routes/web.php')) {
            Route::middleware('web')->group($path.'/routes/web.php');
        }

        if (is_file($path.'/routes/api.php')) {
            Route::middleware('api')
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group($path.'/routes/api.php');
        }
    }

    private function modulePath(): string
    {
        $file = (string) (new ReflectionClass(static::class))->getFileName();

        return dirname($file, 2);
    }
}
