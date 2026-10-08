<?php

declare(strict_types=1);

namespace App\Console\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Renders stubs/starter/*.stub with {{ placeholder }} replacements and writes them,
 * refusing to overwrite existing files.
 */
final readonly class StubRenderer
{
    public function __construct(private Filesystem $files) {}

    /** @param  array<string, string>  $replacements  placeholder (without braces) => value */
    public function render(string $stub, array $replacements): string
    {
        $path = base_path('stubs/starter/'.$stub.'.stub');

        if (! $this->files->exists($path)) {
            throw new RuntimeException("Stub [{$stub}] not found at {$path}.");
        }

        $contents = $this->files->get($path);

        foreach ($replacements as $key => $value) {
            $contents = str_replace('{{ '.$key.' }}', $value, $contents);
        }

        return $contents;
    }

    /**
     * @param  array<string, string>  $replacements
     * @return bool false when the target already exists (nothing written)
     */
    public function write(string $stub, string $target, array $replacements): bool
    {
        if ($this->files->exists($target)) {
            return false;
        }

        $this->files->ensureDirectoryExists(dirname($target));
        $this->files->put($target, $this->render($stub, $replacements));

        if (str_ends_with($target, '.php') && ! str_ends_with($target, '.blade.php')) {
            $this->format([$target]);
        }

        return true;
    }

    /**
     * Apply the project's Pint rules so generated code passes `composer lint` as-is.
     *
     * @param  list<string>  $paths
     */
    public function format(array $paths): void
    {
        $pint = base_path('vendor/bin/pint');

        if ($paths === [] || ! $this->files->exists($pint)) {
            return;
        }

        (new Process([PHP_BINARY, $pint, '--quiet', ...$paths], base_path()))->run();
    }
}
