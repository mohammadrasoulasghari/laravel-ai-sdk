<?php

declare(strict_types=1);

namespace App\Console\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/** A validated StudlyCase module or class name plus its derived forms. */
final readonly class ModuleName
{
    public string $studly;

    public string $kebab;

    public string $snake;

    public function __construct(string $name)
    {
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) !== 1) {
            throw new InvalidArgumentException("Name [{$name}] must be StudlyCase, e.g. Contact or BillingAccount.");
        }

        $this->studly = $name;
        $this->kebab = Str::kebab($name);
        $this->snake = Str::snake($name);
    }

    public function path(string $relative = ''): string
    {
        return app_path('Modules/'.$this->studly.($relative === '' ? '' : '/'.$relative));
    }

    public function namespace(string $relative = ''): string
    {
        return 'App\\Modules\\'.$this->studly.($relative === '' ? '' : '\\'.$relative);
    }
}
