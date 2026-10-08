<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\ModuleName;
use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:module', description: 'Create a module skeleton in app/Modules and register its provider')]
#[Signature('make:module {name : StudlyCase module name, e.g. Contact}')]
final class MakeModuleCommand extends Command
{
    public function handle(StubRenderer $stubs, Filesystem $files): int
    {
        $module = new ModuleName($this->argument('name'));

        if ($files->isDirectory($module->path())) {
            $this->components->error("Module [{$module->studly}] already exists.");

            return self::FAILURE;
        }

        $replacements = ['module' => $module->studly, 'kebab' => $module->kebab];

        $stubs->write('module.provider', $module->path("Providers/{$module->studly}ServiceProvider.php"), $replacements);
        $stubs->write('module.permission', $module->path("Enums/{$module->studly}Permission.php"), $replacements);
        $stubs->write('module.routes-web', $module->path('routes/web.php'), $replacements);
        $stubs->write('module.routes-api', $module->path('routes/api.php'), $replacements);
        $stubs->write('module.lang', $module->path("lang/en/{$module->kebab}.php"), $replacements);
        $stubs->write('module.lang', $module->path("lang/fa/{$module->kebab}.php"), $replacements);

        foreach (['Actions', 'Data', 'Database/Migrations', 'Database/Factories', 'Http/Controllers', 'Http/Requests', 'Models', 'Queries', 'resources/views'] as $dir) {
            $files->ensureDirectoryExists($module->path($dir));
            $files->put($module->path($dir.'/.gitkeep'), '');
        }

        $this->registerProvider($files, $module);

        $this->components->info("Module [{$module->studly}] created in app/Modules/{$module->studly}.");
        $this->components->bulletList([
            "Translate the nav label in lang/fa/{$module->kebab}.php",
            "Add a migration: php artisan make:migration create_{$module->snake}_table --path=app/Modules/{$module->studly}/Database/Migrations",
            "Add actions/DTOs: php artisan make:action {$module->studly} Create{$module->studly}",
            'Follow .claude/skills/new-module/SKILL.md, then run composer check',
        ]);

        return self::SUCCESS;
    }

    private function registerProvider(Filesystem $files, ModuleName $module): void
    {
        $path = base_path('bootstrap/providers.php');
        $contents = $files->get($path);
        $class = $module->namespace("Providers\\{$module->studly}ServiceProvider");

        if (str_contains($contents, $class)) {
            return;
        }

        $contents = (string) preg_replace('/(\n\s*\/\/ modules\n)/', "$1    \\{$class}::class,\n", $contents, 1);
        $files->put($path, $contents);
    }
}
