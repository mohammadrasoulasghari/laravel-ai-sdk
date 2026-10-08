<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\ModuleName;
use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action', description: 'Create an Action (+ its DTO and a feature test) in a module')]
#[Signature('make:action
        {module : Existing module, e.g. Blog}
        {name : Verb-noun class name without suffix, e.g. ArchivePost}
        {--data= : DTO class to accept (default: <Name>Data, created if missing)}')]
final class MakeActionCommand extends Command
{
    public function handle(StubRenderer $stubs): int
    {
        $module = new ModuleName($this->argument('module'));
        $action = new ModuleName($this->argument('name'));
        $option = $this->option('data');
        $data = is_string($option) && $option !== '' ? $option : $action->studly.'Data';

        if (str_ends_with($action->studly, 'Action')) {
            $this->components->error('Action names have no "Action" suffix (conventions §2).');

            return self::FAILURE;
        }

        $replacements = [
            'module' => $module->studly,
            'class' => $action->studly,
            'data' => $data,
            'description' => Str::headline($action->studly).'.',
            'sentence' => Str::lower(Str::headline($action->studly)),
        ];

        $created = $stubs->write('action', $module->path("Actions/{$action->studly}.php"), $replacements);
        $stubs->write('dto', $module->path("Data/{$data}.php"), [...$replacements, 'class' => $data]);
        $stubs->write('action-test', base_path("tests/Feature/Modules/{$module->studly}/Actions/{$action->studly}Test.php"), $replacements);

        if (! $created) {
            $this->components->error("Action [{$action->studly}] already exists.");

            return self::FAILURE;
        }

        $this->components->info("Action [{$module->namespace('Actions\\'.$action->studly)}] created with {$data} and a test.");

        return self::SUCCESS;
    }
}
