<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Exceptions\PostNotPublishedException;
use App\Modules\Blog\Models\Post;

final readonly class UnpublishPost
{
    /** @throws PostNotPublishedException */
    public function handle(Post $post): Post
    {
        if ($post->status !== PostStatus::Published) {
            throw new PostNotPublishedException("Post {$post->id} is not published.");
        }

        $post->status = PostStatus::Draft;
        $post->published_at = null;
        $post->save();

        return $post;
    }
}
