<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Support\StubRenderer;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:ui-component', description: 'Create an anonymous UI kit component (<x-ui.name>)')]
#[Signature('make:ui-component {name : kebab-case name, e.g. tabs}')]
final class MakeUiComponentCommand extends Command
{
    public function handle(StubRenderer $stubs): int
    {
        $name = Str::kebab($this->argument('name'));

        if (preg_match('/^[a-z][a-z0-9-]*$/', $name) !== 1) {
            $this->components->error('Component names are kebab-case, e.g. "date-picker".');

            return self::FAILURE;
        }

        if (! $stubs->write('ui-component', resource_path("views/components/ui/{$name}.blade.php"), ['name' => $name])) {
            $this->components->error("Component [ui.{$name}] already exists.");

            return self::FAILURE;
        }

        $this->components->info("Component <x-ui.{$name}> created in resources/views/components/ui/{$name}.blade.php.");
        $this->components->warn('Add an example to resources/views/styleguide.blade.php (blade-component skill).');

        return self::SUCCESS;
    }
}
