<?php

declare(strict_types=1);

namespace App\Modules\Roles\Queries;

use App\Modules\Roles\Contracts\RoleCatalog;
use Illuminate\Contracts\Translation\Translator;
use Spatie\Permission\Models\Role;

final readonly class RoleOptionsQuery implements RoleCatalog
{
    public function __construct(private Translator $translator) {}

    /** @return array<string, string> */
    public function handle(): array
    {
        $options = [];

        foreach (Role::query()->orderBy('name')->get() as $role) {
            $key = 'roles::roles.names.'.$role->name;
            $label = $this->translator->get($key);
            $options[$role->name] = is_string($label) && $label !== $key ? $label : $role->name;
        }

        return $options;
    }

    public function options(): array
    {
        return $this->handle();
    }
}
