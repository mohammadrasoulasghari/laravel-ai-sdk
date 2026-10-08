<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Models\Post;

final readonly class DeletePost
{
    public function handle(Post $post): void
    {
        $post->delete();
    }
}
