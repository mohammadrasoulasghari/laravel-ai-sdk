<?php

declare(strict_types=1);

namespace App\Modules\Blog\Data;

use App\Modules\Blog\Enums\PostSort;

final readonly class PostFiltersData
{
    public function __construct(
        public ?string $search = null,
        public ?string $categorySlug = null,
        public PostSort $sort = PostSort::NewestFirst,
        public int $perPage = 15,
    ) {}
}
