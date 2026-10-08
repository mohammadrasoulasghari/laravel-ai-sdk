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

        foreach (['Actions', 'Data', 'Database/Migrations', 'Database/Factories', 'Database/Seeders', 'Events', 'Http/Controllers', 'Http/Requests', 'Listeners', 'Models', 'Notifications', 'Policies', 'Queries', 'resources/views'] as $dir) {
            $files->ensureDirectoryExists($module->path($dir));
            $files->put($module->path($dir.'/.gitkeep'), '');
        }

        $this->registerProvider($files, $stubs, $module);

        $this->components->info("Module [{$module->studly}] created in app/Modules/{$module->studly}.");
        $this->components->bulletList([
            "Translate the nav label in lang/fa/{$module->kebab}.php",
            "Add a migration: php artisan make:migration create_{$module->snake}_<resources>_table --path=app/Modules/{$module->studly}/Database/Migrations",
            "Add actions: php artisan make:action {$module->studly} <VerbNoun> [--no-data]",
            'Delete routes/api.php if the module has no API, and remove folders you do not use (.gitkeep)',
            'Follow .claude/skills/new-module/SKILL.md, then run composer check',
        ]);

        return self::SUCCESS;
    }

    private function registerProvider(Filesystem $files, StubRenderer $stubs, ModuleName $module): void
    {
        $path = base_path('bootstrap/providers.php');
        $contents = $files->get($path);
        $class = $module->namespace("Providers\\{$module->studly}ServiceProvider");

        if (str_contains($contents, $class)) {
            return;
        }

        $short = $module->studly.'ServiceProvider';

        // Import the provider next to the other `use` lines…
        $contents = (string) preg_replace('/^(use [^;]+;\n)(?!use )/m', "$1use {$class};\n", $contents, 1);

        // …and add it to the "// modules" block, keeping that block alphabetical.
        $contents = (string) preg_replace_callback(
            '/(\/\/ modules\n)((?:    [A-Za-z]+::class,\n)*)/',
            static function (array $match) use ($short): string {
                $lines = array_filter(explode("\n", $match[2]));
                $lines[] = "    {$short}::class,";
                sort($lines);

                return $match[1].implode("\n", $lines)."\n";
            },
            $contents,
            1,
        );

        $files->put($path, $contents);
        $stubs->format([$path]);
    }
}
