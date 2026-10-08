<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    $this->root = storage_path('framework/testing/starter-init-'.uniqid());
    $files = new Filesystem;
    $files->ensureDirectoryExists($this->root.'/bootstrap');
    $files->ensureDirectoryExists($this->root.'/database/seeders');
    $files->ensureDirectoryExists($this->root.'/app/Modules/Blog/Models');
    $files->ensureDirectoryExists($this->root.'/tests/Feature/Modules/Blog');
    $files->put($this->root.'/composer.json', json_encode(['name' => 'acme/laravel-ai-starter', 'description' => 'Starter', 'keywords' => ['x']]));
    $files->put($this->root.'/package.json', json_encode(['private' => true]));
    $files->put($this->root.'/.env.example', "APP_NAME=\"Laravel AI Starter\"\nAPP_URL=http://localhost\n");
    $files->put($this->root.'/README.md', "# Laravel AI-First Starter Kit\n\nBody\n");
    $files->put($this->root.'/CHANGELOG.md', "# Changelog\n\n## [1.0.0]\n");
    $files->put($this->root.'/bootstrap/providers.php', "<?php\nreturn [\n    AppServiceProvider::class,\n    BlogServiceProvider::class,\n];\n");
    $files->put($this->root.'/database/seeders/DatabaseSeeder.php', "<?php\n\$this->call([\n    RolesSeeder::class,\n    BlogSeeder::class,\n]);\n");
    $files->put($this->root.'/app/Modules/Blog/Models/Post.php', '<?php');
});

afterEach(function (): void {
    (new Filesystem)->deleteDirectory($this->root);
});

it('renames the project non-interactively', function (): void {
    $this->artisan('starter:init', [
        '--name' => 'Acme Shop',
        '--package' => 'acme/shop',
        '--url' => 'https://shop.test',
        '--path' => $this->root,
        '--no-interaction' => true,
    ])->assertSuccessful();

    $composer = json_decode((string) file_get_contents($this->root.'/composer.json'), true);

    expect($composer['name'])->toBe('acme/shop')
        ->and($composer['description'])->toBe('Acme Shop')
        ->and($composer)->not->toHaveKey('keywords')
        ->and(json_decode((string) file_get_contents($this->root.'/package.json'), true)['name'])->toBe('shop')
        ->and(file_get_contents($this->root.'/.env.example'))->toContain('APP_NAME="Acme Shop"')->toContain('APP_URL=https://shop.test')
        ->and(file_get_contents($this->root.'/README.md'))->toStartWith('# Acme Shop')
        ->and(file_get_contents($this->root.'/CHANGELOG.md'))->toContain('## [Unreleased]')->not->toContain('1.0.0')
        ->and($this->root.'/app/Modules/Blog')->toBeDirectory();
});

it('removes the example module on request', function (): void {
    $this->artisan('starter:init', [
        '--name' => 'Acme',
        '--package' => 'acme/app',
        '--url' => 'http://localhost',
        '--remove-example' => true,
        '--path' => $this->root,
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect($this->root.'/app/Modules/Blog')->not->toBeDirectory()
        ->and($this->root.'/tests/Feature/Modules/Blog')->not->toBeDirectory()
        ->and(file_get_contents($this->root.'/bootstrap/providers.php'))->not->toContain('Blog')->toContain('AppServiceProvider')
        ->and(file_get_contents($this->root.'/database/seeders/DatabaseSeeder.php'))->not->toContain('BlogSeeder');
});

it('changes nothing in dry-run mode', function (): void {
    $before = file_get_contents($this->root.'/composer.json');

    $this->artisan('starter:init', [
        '--name' => 'Acme',
        '--package' => 'acme/app',
        '--url' => 'http://localhost',
        '--remove-example' => true,
        '--dry-run' => true,
        '--path' => $this->root,
        '--no-interaction' => true,
    ])->expectsOutputToContain('dry run')->assertSuccessful();

    expect(file_get_contents($this->root.'/composer.json'))->toBe($before)
        ->and($this->root.'/app/Modules/Blog')->toBeDirectory();
});

it('rejects an invalid package name', function (): void {
    $this->artisan('starter:init', [
        '--name' => 'Acme',
        '--package' => 'Not Valid',
        '--url' => 'http://localhost',
        '--path' => $this->root,
        '--no-interaction' => true,
    ])->assertFailed();
});
