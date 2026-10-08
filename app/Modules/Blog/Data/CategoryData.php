<?php

declare(strict_types=1);

namespace App\Modules\Blog\Data;

use App\Modules\Blog\ValueObjects\Slug;

final readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?Slug $slug = null,
    ) {}
}
