<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\ModuleName;
use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:dto', description: 'Create an immutable DTO (final readonly, *Data) in a module')]
#[Signature('make:dto {module : Existing module} {name : Class name; "Data" suffix is added if missing}')]
final class MakeDtoCommand extends Command
{
    public function handle(StubRenderer $stubs): int
    {
        $module = new ModuleName($this->argument('module'));
        $name = new ModuleName($this->argument('name'));
        $class = str_ends_with($name->studly, 'Data') ? $name->studly : $name->studly.'Data';

        if (! $stubs->write('dto', $module->path("Data/{$class}.php"), ['module' => $module->studly, 'class' => $class])) {
            $this->components->error("DTO [{$class}] already exists.");

            return self::FAILURE;
        }

        $this->components->info("DTO [{$module->namespace('Data\\'.$class)}] created.");

        return self::SUCCESS;
    }
}
