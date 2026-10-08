<?php

declare(strict_types=1);

use App\Shared\Exceptions\DomainException;
use App\Shared\Providers\ModuleServiceProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Tests\modules;

foreach (modules() as $module) {
    $ns = "App\\Modules\\{$module}";

    arch("{$module}: actions are final, single-purpose handle() classes")
        ->expect("{$ns}\\Actions")
        ->toBeFinal()
        ->toHaveMethod('handle')
        ->not->toHaveSuffix('Action');

    arch("{$module}: DTOs are final readonly data classes")
        ->expect("{$ns}\\Data")
        ->toBeFinal()
        ->toBeReadonly()
        ->toHaveSuffix('Data');

    arch("{$module}: value objects are final and readonly")
        ->expect("{$ns}\\ValueObjects")
        ->toBeFinal()
        ->toBeReadonly();

    arch("{$module}: controllers are thin")
        ->expect("{$ns}\\Http\\Controllers")
        ->toBeFinal()
        ->toHaveSuffix('Controller')
        ->not->toUse([
            DB::class,
            Builder::class,
            Illuminate\Database\Query\Builder::class,
            Validator::class,
        ]);

    arch("{$module}: form requests extend FormRequest")
        ->expect("{$ns}\\Http\\Requests")
        ->toExtend(FormRequest::class)
        ->toHaveSuffix('Request');

    arch("{$module}: API resources extend JsonResource")
        ->expect("{$ns}\\Http\\Resources")
        ->toExtend(JsonResource::class)
        ->toHaveSuffix('Resource');

    arch("{$module}: policies are suffixed")
        ->expect("{$ns}\\Policies")
        ->toHaveSuffix('Policy');

    arch("{$module}: queries are suffixed")
        ->expect("{$ns}\\Queries")
        ->toBeFinal()
        ->toHaveSuffix('Query');

    arch("{$module}: models extend Eloquent")
        ->expect("{$ns}\\Models")
        ->toExtend(Model::class);

    arch("{$module}: enums are enums")
        ->expect("{$ns}\\Enums")
        ->toBeEnums();

    arch("{$module}: exceptions are domain exceptions")
        ->expect("{$ns}\\Exceptions")
        ->toExtend(DomainException::class)
        ->toHaveSuffix('Exception');

    arch("{$module}: jobs are queued")
        ->expect("{$ns}\\Jobs")
        ->toImplement(ShouldQueue::class);

    arch("{$module}: provider extends ModuleServiceProvider")
        ->expect("{$ns}\\Providers")
        ->toExtend(ModuleServiceProvider::class);

    arch("{$module}: domain layer uses no facades")
        ->expect([
            "{$ns}\\Actions",
            "{$ns}\\Data",
            "{$ns}\\Enums",
            "{$ns}\\Events",
            "{$ns}\\Models",
            "{$ns}\\Queries",
            "{$ns}\\ValueObjects",
        ])
        ->not->toUse('Illuminate\Support\Facades');

    arch("{$module}: actions are only used by controllers, commands, jobs, listeners and other actions")
        ->expect("{$ns}\\Actions")
        ->toOnlyBeUsedIn([
            "{$ns}\\Actions",
            "{$ns}\\Http\\Controllers",
            "{$ns}\\Console",
            "{$ns}\\Jobs",
            "{$ns}\\Listeners",
            "{$ns}\\Providers",
            "{$ns}\\database",
            'App\Console',
        ]);
}
