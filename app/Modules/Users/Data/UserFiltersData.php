<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

final readonly class UserFiltersData
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
    ) {}
}
