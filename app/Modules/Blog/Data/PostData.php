<?php

declare(strict_types=1);

namespace App\Modules\Blog\Data;

use App\Modules\Blog\ValueObjects\Slug;

/** Editable fields of a post. Slug is generated from the title when null. */
final readonly class PostData
{
    public function __construct(
        public string $title,
        public string $body,
        public string $categoryId,
        public ?string $excerpt = null,
        public ?Slug $slug = null,
    ) {}
}
