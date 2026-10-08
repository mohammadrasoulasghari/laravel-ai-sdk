<?php

declare(strict_types=1);

namespace App\Modules\Blog\Listeners;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Notifications\PostPublishedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Queued after commit (delayed until publication for scheduled posts) and retry-safe:
 * re-reads the post and does nothing if it was unpublished, rescheduled or deleted meanwhile. Notifying is the final step, so a retry only
 * happens when sending itself failed.
 */
#[Backoff([10, 60, 300])]
#[Tries(3)]
final class NotifyAuthorOfPublication implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    /** Scheduled posts: deliver the job when the post actually goes live. */
    public function withDelay(PostPublished $event): int
    {
        return max(0, (int) CarbonImmutable::now()->diffInSeconds(CarbonImmutable::parse($event->publishedAt), false));
    }

    public function handle(PostPublished $event): void
    {
        $post = Post::query()->with('author')->find($event->postId);

        if ($post === null || $post->author === null || $post->status !== PostStatus::Published || $post->published_at?->isFuture() !== false) {
            return;
        }

        $post->author->notify(new PostPublishedNotification($post->id, $post->title, $post->slug->value));
    }
}
