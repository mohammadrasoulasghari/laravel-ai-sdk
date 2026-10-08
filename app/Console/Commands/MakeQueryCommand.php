<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\ModuleName;
use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:query', description: 'Create a Query object (*Query) in a module')]
#[Signature('make:query {module : Existing module} {name : Class name; "Query" suffix is added if missing}')]
final class MakeQueryCommand extends Command
{
    public function handle(StubRenderer $stubs): int
    {
        $module = new ModuleName($this->argument('module'));
        $name = new ModuleName($this->argument('name'));
        $class = str_ends_with($name->studly, 'Query') ? $name->studly : $name->studly.'Query';

        $written = $stubs->write('query', $module->path("Queries/{$class}.php"), [
            'module' => $module->studly,
            'class' => $class,
            'description' => Str::headline(Str::beforeLast($class, 'Query')).'.',
        ]);

        if (! $written) {
            $this->components->error("Query [{$class}] already exists.");

            return self::FAILURE;
        }

        $this->components->info("Query [{$module->namespace('Queries\\'.$class)}] created.");

        return self::SUCCESS;
    }
}
