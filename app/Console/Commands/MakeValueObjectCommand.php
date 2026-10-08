<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\ModuleName;
use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:value-object', description: 'Create a final readonly Value Object (+ unit test) in a module')]
#[Signature('make:value-object {module : Existing module} {name : Class name, e.g. Slug}')]
final class MakeValueObjectCommand extends Command
{
    public function handle(StubRenderer $stubs): int
    {
        $module = new ModuleName($this->argument('module'));
        $name = new ModuleName($this->argument('name'));
        $replacements = ['module' => $module->studly, 'class' => $name->studly];

        if (! $stubs->write('value-object', $module->path("ValueObjects/{$name->studly}.php"), $replacements)) {
            $this->components->error("Value object [{$name->studly}] already exists.");

            return self::FAILURE;
        }

        $stubs->write('value-object-test', base_path("tests/Unit/Modules/{$module->studly}/{$name->studly}Test.php"), $replacements);

        $this->components->info("Value object [{$module->namespace('ValueObjects\\'.$name->studly)}] created with a unit test.");

        return self::SUCCESS;
    }
}
