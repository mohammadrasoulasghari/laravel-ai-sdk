<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

/**
 * Turns a fresh clone of the starter kit into a new project.
 *
 *   php artisan starter:init                                  (interactive)
 *   php artisan starter:init --name="Acme Shop" --package=acme/shop --url=https://shop.test \
 *       --remove-example --fresh-git --no-interaction       (CI / scripted)
 */
#[AsCommand(name: 'starter:init', description: 'Rename the starter kit into a new project (name, package, URL, keys, git history)')]
#[Signature('starter:init
        {--name= : Human project name, e.g. "Acme Shop"}
        {--package= : Composer package, vendor/name, e.g. acme/shop}
        {--url= : APP_URL, e.g. https://acme.test}
        {--remove-example : Delete the Blog reference module and its tests}
        {--fresh-git : Delete git history and create an initial commit}
        {--dry-run : Show what would change without writing}
        {--path= : Project root (defaults to base_path(); used by tests)}')]
final class StarterInitCommand extends Command
{
    private string $root = '';

    public function handle(Filesystem $files): int
    {
        $this->root = rtrim($this->stringOption('path') ?? base_path(), '/');

        $name = $this->stringOption('name') ?? text('Project name', default: 'My App', required: true);
        $package = $this->stringOption('package') ?? text('Composer package (vendor/name)', default: Str::slug($name).'/'.Str::slug($name), required: true);
        $url = $this->stringOption('url') ?? text('Local URL', default: 'http://localhost', required: true);
        $removeExample = $this->option('remove-example') === true
            || (! $this->option('no-interaction') && $this->input->isInteractive() && confirm('Remove the Blog example module?', default: false));
        $freshGit = $this->option('fresh-git') === true
            || (! $this->option('no-interaction') && $this->input->isInteractive() && confirm('Reset git history?', default: false));

        if (preg_match('#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$#', $package) !== 1) {
            $this->components->error("Invalid Composer package name [{$package}]. Use lowercase vendor/name.");

            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run') === true;
        $changes = [
            'composer.json' => fn (string $json): string => $this->renameComposer($json, $package, $name),
            'package.json' => fn (string $json): string => $this->renamePackage($json, $package),
            '.env.example' => fn (string $env): string => $this->setEnv($env, ['APP_NAME' => "\"{$name}\"", 'APP_URL' => $url]),
            '.env' => fn (string $env): string => $this->setEnv($env, ['APP_NAME' => "\"{$name}\"", 'APP_URL' => $url]),
            'README.md' => fn (string $readme): string => (string) preg_replace('/^# .+$/m', "# {$name}", $readme, 1),
            'CHANGELOG.md' => fn (): string => "# Changelog\n\nAll notable changes to {$name} are documented here (Keep a Changelog, SemVer).\n\n## [Unreleased]\n\n### Added\n- Project created from the Laravel AI-First Starter Kit.\n",
        ];

        foreach ($changes as $file => $transform) {
            $path = $this->root.'/'.$file;

            if (! $files->exists($path)) {
                continue;
            }

            $this->components->task("Update {$file}", function () use ($files, $path, $transform, $dryRun): bool {
                if (! $dryRun) {
                    $files->put($path, $transform($files->get($path)));
                }

                return true;
            });
        }

        if ($removeExample) {
            $this->components->task('Remove Blog example module', fn (): bool => $dryRun || $this->removeExample($files));
        }

        if (! $dryRun && $this->stringOption('path') === null) {
            $this->call('key:generate', ['--force' => true]);
        }

        if ($freshGit) {
            $this->components->task('Reset git history', fn (): bool => $dryRun || $this->resetGit($files, $name));
        }

        $this->newLine();
        $this->components->info("{$name} is ready".($dryRun ? ' (dry run — nothing written)' : '').'.');
        $this->components->bulletList([
            'make setup               # Docker: install, migrate + seed, build assets',
            'composer check           # every quality gate (same as CI)',
            'php artisan make:module  # your first module — or /feature in Claude Code',
            'Read CLAUDE.md and docs/architecture.md',
        ]);

        return self::SUCCESS;
    }

    private function renameComposer(string $json, string $package, string $name): string
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $data['name'] = $package;
        $data['description'] = $name;
        unset($data['keywords']);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    }

    private function renamePackage(string $json, string $package): string
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $data['name'] = Str::after($package, '/');

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    }

    /** @param  array<string, string>  $values */
    private function setEnv(string $env, array $values): string
    {
        foreach ($values as $key => $value) {
            $line = $key.'='.$value;
            $env = preg_match("/^{$key}=.*$/m", $env) === 1
                ? (string) preg_replace("/^{$key}=.*$/m", str_replace('$', '\$', $line), $env)
                : $env."\n".$line."\n";
        }

        return $env;
    }

    private function removeExample(Filesystem $files): bool
    {
        foreach (['app/Modules/Blog', 'tests/Feature/Modules/Blog', 'tests/Unit/Modules/Blog'] as $dir) {
            $files->deleteDirectory($this->root.'/'.$dir);
        }

        foreach (['bootstrap/providers.php', 'database/seeders/DatabaseSeeder.php'] as $file) {
            $path = $this->root.'/'.$file;

            if ($files->exists($path)) {
                $lines = array_filter(
                    explode("\n", $files->get($path)),
                    static fn (string $line): bool => ! str_contains($line, 'BlogServiceProvider') && ! str_contains($line, 'BlogSeeder'),
                );
                $files->put($path, implode("\n", $lines));
            }
        }

        return true;
    }

    private function resetGit(Filesystem $files, string $name): bool
    {
        $files->deleteDirectory($this->root.'/.git');

        foreach ([['git', 'init', '-q', '-b', 'main'], ['git', 'add', '-A'], ['git', 'commit', '-q', '--no-verify', '-m', "chore: start {$name} from laravel ai-first starter kit"]] as $command) {
            $process = new Process($command, $this->root);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->components->error(trim($process->getErrorOutput()));

                return false;
            }
        }

        return true;
    }

    private function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
