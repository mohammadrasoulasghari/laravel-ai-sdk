<?php

declare(strict_types=1);

use App\Modules\Blog\Actions\PublishPost;
use App\Modules\Blog\Actions\UnpublishPost;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Listeners\NotifyAuthorOfPublication;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Notifications\PostPublishedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
});

it('listens for PostPublished', function (): void {
    Event::fake();

    Event::assertListening(PostPublished::class, NotifyAuthorOfPublication::class);
});

it('is queued after the transaction commits', function (): void {
    expect(new NotifyAuthorOfPublication)->toBeInstanceOf(ShouldQueueAfterCommit::class);
});

it('notifies the author when a post is published', function (): void {
    $post = Post::factory()->create();

    resolve(PublishPost::class)->handle($post);

    Notification::assertSentTo(
        $post->author,
        PostPublishedNotification::class,
        fn (PostPublishedNotification $notification): bool => $notification->postId === $post->id
            && $notification->title === $post->title
            && $notification->slug === $post->slug->value,
    );
    Notification::assertSentTimes(PostPublishedNotification::class, 1);
});

it('does nothing when the post was unpublished before the listener ran', function (): void {
    $post = Post::factory()->published()->create();
    resolve(UnpublishPost::class)->handle($post);

    (new NotifyAuthorOfPublication)->handle(new PostPublished($post->id, $post->author_id, now()->toIso8601String()));

    Notification::assertNothingSent();
});

it('does nothing for a draft', function (): void {
    $post = Post::factory()->create();

    (new NotifyAuthorOfPublication)->handle(new PostPublished($post->id, $post->author_id, now()->toIso8601String()));

    Notification::assertNothingSent();
});

it('does nothing when the post was deleted meanwhile', function (): void {
    $post = Post::factory()->published()->create();
    $event = new PostPublished($post->id, $post->author_id, now()->toIso8601String());
    $post->delete();

    (new NotifyAuthorOfPublication)->handle($event);

    Notification::assertNothingSent();
});

it('does not tell the author a scheduled post is live before its date', function (): void {
    $post = Post::factory()->create();

    resolve(PublishPost::class)->handle($post, CarbonImmutable::now()->addWeek());

    Notification::assertNothingSent();
});
