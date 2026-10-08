<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

const PROBE = 'ZzGeneratorProbe';

beforeEach(function (): void {
    $this->providers = (string) file_get_contents(base_path('bootstrap/providers.php'));
});

afterEach(function (): void {
    $files = new Filesystem;
    $files->deleteDirectory(app_path('Modules/'.PROBE));
    $files->deleteDirectory(base_path('tests/Feature/Modules/'.PROBE));
    $files->deleteDirectory(base_path('tests/Unit/Modules/'.PROBE));
    $files->delete(resource_path('views/components/ui/zz-probe.blade.php'));
    file_put_contents(base_path('bootstrap/providers.php'), $this->providers);
});

it('scaffolds a module and registers its provider', function (): void {
    $this->artisan('make:module', ['name' => PROBE])->assertSuccessful();

    $base = app_path('Modules/'.PROBE);

    expect("{$base}/Providers/".PROBE.'ServiceProvider.php')->toBeFile()
        ->and("{$base}/Enums/".PROBE.'Permission.php')->toBeFile()
        ->and("{$base}/routes/web.php")->toBeFile()
        ->and("{$base}/routes/api.php")->toBeFile()
        ->and("{$base}/lang/en/zz-generator-probe.php")->toBeFile()
        ->and("{$base}/lang/fa/zz-generator-probe.php")->toBeFile()
        ->and("{$base}/Database/Migrations")->toBeDirectory()
        ->and(file_get_contents(base_path('bootstrap/providers.php')))->toContain(PROBE.'ServiceProvider::class')
        ->and(file_get_contents("{$base}/Enums/".PROBE.'Permission.php'))->toContain("'zz-generator-probe.manage'");

    $this->artisan('make:module', ['name' => PROBE])->assertFailed();
});

it('rejects non-StudlyCase names', function (): void {
    $this->artisan('make:module', ['name' => 'bad-name']);
})->throws(InvalidArgumentException::class);

it('generates an action with its DTO and test', function (): void {
    $this->artisan('make:module', ['name' => PROBE])->assertSuccessful();
    $this->artisan('make:action', ['module' => PROBE, 'name' => 'ArchiveThing'])->assertSuccessful();

    $action = (string) file_get_contents(app_path('Modules/'.PROBE.'/Actions/ArchiveThing.php'));

    expect($action)->toContain('final readonly class ArchiveThing')->toContain('public function handle(ArchiveThingData $data): ArchiveThingData')
        ->and(app_path('Modules/'.PROBE.'/Data/ArchiveThingData.php'))->toBeFile()
        ->and(base_path('tests/Feature/Modules/'.PROBE.'/Actions/ArchiveThingTest.php'))->toBeFile();

    $this->artisan('make:action', ['module' => PROBE, 'name' => 'ArchiveThing'])->assertFailed();
    $this->artisan('make:action', ['module' => PROBE, 'name' => 'DoThingAction'])->assertFailed();
});

it('generates DTOs, queries and value objects with conventional suffixes', function (): void {
    $this->artisan('make:dto', ['module' => PROBE, 'name' => 'Filters'])->assertSuccessful();
    $this->artisan('make:query', ['module' => PROBE, 'name' => 'Things'])->assertSuccessful();
    $this->artisan('make:value-object', ['module' => PROBE, 'name' => 'Code'])->assertSuccessful();

    expect(app_path('Modules/'.PROBE.'/Data/FiltersData.php'))->toBeFile()
        ->and(app_path('Modules/'.PROBE.'/Queries/ThingsQuery.php'))->toBeFile()
        ->and(app_path('Modules/'.PROBE.'/ValueObjects/Code.php'))->toBeFile()
        ->and(base_path('tests/Unit/Modules/'.PROBE.'/CodeTest.php'))->toBeFile();

    $this->artisan('make:dto', ['module' => PROBE, 'name' => 'FiltersData'])->assertFailed();
    $this->artisan('make:query', ['module' => PROBE, 'name' => 'ThingsQuery'])->assertFailed();
    $this->artisan('make:value-object', ['module' => PROBE, 'name' => 'Code'])->assertFailed();
});

it('generates a UI kit component', function (): void {
    $this->artisan('make:ui-component', ['name' => 'zz-probe'])->assertSuccessful();

    expect(resource_path('views/components/ui/zz-probe.blade.php'))->toBeFile();

    $this->artisan('make:ui-component', ['name' => 'zz-probe'])->assertFailed();
    $this->artisan('make:ui-component', ['name' => '9bad'])->assertFailed();
});

it('generates an action without a DTO', function (): void {
    $this->artisan('make:module', ['name' => PROBE])->assertSuccessful();
    $this->artisan('make:action', ['module' => PROBE, 'name' => 'DeleteThing', '--no-data' => true])->assertSuccessful();

    expect(file_get_contents(app_path('Modules/'.PROBE.'/Actions/DeleteThing.php')))->toContain('public function handle(Model $model): Model')
        ->and(app_path('Modules/'.PROBE.'/Data/DeleteThingData.php'))->not->toBeFile();
});

it('registers the provider as an imported, sorted entry', function (): void {
    $this->artisan('make:module', ['name' => PROBE])->assertSuccessful();

    $providers = (string) file_get_contents(base_path('bootstrap/providers.php'));

    expect($providers)->toContain('use App\\Modules\\'.PROBE.'\\Providers\\'.PROBE.'ServiceProvider;')
        ->and($providers)->toContain('    '.PROBE.'ServiceProvider::class,')
        ->and(mb_strpos($providers, 'UsersServiceProvider::class'))->toBeLessThan(mb_strpos($providers, PROBE.'ServiceProvider::class'));
});
