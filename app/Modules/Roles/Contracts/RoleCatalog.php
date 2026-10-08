<?php

declare(strict_types=1);

namespace App\Modules\Roles\Contracts;

/** Read-only view of available roles for other modules (e.g. user edit form). */
interface RoleCatalog
{
    /** @return array<string, string> role name => translated label */
    public function options(): array;
}
