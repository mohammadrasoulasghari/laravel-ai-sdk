<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Exceptions\PostAlreadyPublishedException;
use App\Modules\Blog\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;

/**
 * Publishes a draft now, or schedules it when $at is in the future.
 * PostPublished is dispatched after the transaction commits.
 */
final readonly class PublishPost
{
    public function __construct(
        private ConnectionInterface $db,
        private Dispatcher $events,
    ) {}

    /** @throws PostAlreadyPublishedException */
    public function handle(Post $post, ?CarbonImmutable $at = null): Post
    {
        if ($post->status === PostStatus::Published) {
            throw new PostAlreadyPublishedException("Post {$post->id} is already published.");
        }

        $this->db->transaction(function () use ($post, $at): void {
            $post->status = PostStatus::Published;
            $post->published_at = $at ?? CarbonImmutable::now();
            $post->save();
        });

        $this->events->dispatch(new PostPublished($post->id, $post->author_id, ($post->published_at ?? CarbonImmutable::now())->toIso8601String()));

        return $post;
    }
}
