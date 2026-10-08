<?php

declare(strict_types=1);

namespace App\Shared\Localization;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;

/**
 * Single source of truth for supported locales (config/locales.php).
 */
final readonly class Locales
{
    public function __construct(
        private Config $config,
        private Application $app,
    ) {}

    /** @return array<string, string> locale code => native name */
    public function names(): array
    {
        return array_map(static fn (array $locale): string => (string) $locale['name'], $this->supported());
    }

    public function isSupported(string $locale): bool
    {
        return array_key_exists($locale, $this->supported());
    }

    public function current(): string
    {
        return $this->app->getLocale();
    }

    public function direction(?string $locale = null): Direction
    {
        return Direction::from((string) ($this->supported()[$locale ?? $this->current()]['dir'] ?? 'ltr'));
    }

    public function calendar(?string $locale = null): Calendar
    {
        return Calendar::from((string) ($this->supported()[$locale ?? $this->current()]['calendar'] ?? 'gregorian'));
    }

    /** @return array<string, array{name: string, dir: string, calendar: string}> */
    private function supported(): array
    {
        /** @var array<string, array{name: string, dir: string, calendar: string}> $supported */
        $supported = $this->config->array('locales.supported');

        return $supported;
    }
}
