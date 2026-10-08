<?php

declare(strict_types=1);

namespace App\Modules\Roles\Data;

final readonly class RolePermissionsData
{
    /** @param  list<string>  $permissions */
    public function __construct(public array $permissions) {}
}
