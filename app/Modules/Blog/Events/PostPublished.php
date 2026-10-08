<?php

declare(strict_types=1);

namespace App\Modules\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;

/** Public event: other modules may listen (search indexing, newsletters, …). */
final readonly class PostPublished
{
    use Dispatchable;

    public function __construct(
        public string $postId,
        public ?string $authorId,
        /** ISO-8601; in the future for scheduled posts. */
        public string $publishedAt,
    ) {}
}
