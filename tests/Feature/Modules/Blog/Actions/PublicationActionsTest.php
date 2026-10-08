<?php

declare(strict_types=1);

use App\Modules\Blog\Actions\PublishPost;
use App\Modules\Blog\Actions\UnpublishPost;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Exceptions\PostAlreadyPublishedException;
use App\Modules\Blog\Exceptions\PostNotPublishedException;
use App\Modules\Blog\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

describe('PublishPost', function (): void {
    beforeEach(function (): void {
        Event::fake([PostPublished::class]);
    });

    it('publishes a draft now and announces it', function (): void {
        $this->freezeSecond();
        $post = Post::factory()->create();

        $published = resolve(PublishPost::class)->handle($post);

        expect($published->status)->toBe(PostStatus::Published)
            ->and($published->published_at?->equalTo(now()))->toBeTrue()
            ->and($post->refresh()->status)->toBe(PostStatus::Published)
            ->and(Post::query()->published()->whereKey($post->id)->exists())->toBeTrue();

        Event::assertDispatchedTimes(PostPublished::class, 1);
        Event::assertDispatched(
            PostPublished::class,
            fn (PostPublished $event): bool => $event->postId === $post->id && $event->authorId === $post->author_id,
        );
    });

    it('schedules a post for a future date without making it public', function (): void {
        $post = Post::factory()->create();
        $at = CarbonImmutable::now()->addDay()->startOfSecond();

        resolve(PublishPost::class)->handle($post, $at);

        $post->refresh();

        expect($post->status)->toBe(PostStatus::Published)
            ->and($post->published_at?->equalTo($at))->toBeTrue()
            ->and(Post::query()->published()->whereKey($post->id)->exists())->toBeFalse();

        $this->travelTo($at->addMinute());

        expect(Post::query()->published()->whereKey($post->id)->exists())->toBeTrue();
    });

    it('refuses to publish twice', function (): void {
        $post = Post::factory()->published()->create();
        $publishedAt = $post->published_at;

        expect(fn (): Post => resolve(PublishPost::class)->handle($post))
            ->toThrow(PostAlreadyPublishedException::class, "Post {$post->id} is already published.");

        expect($post->refresh()->published_at?->equalTo($publishedAt))->toBeTrue();

        Event::assertNotDispatched(PostPublished::class);
    });
});

describe('UnpublishPost', function (): void {
    it('moves a published post back to draft', function (): void {
        $post = Post::factory()->published()->create();

        resolve(UnpublishPost::class)->handle($post);

        $post->refresh();

        expect($post->status)->toBe(PostStatus::Draft)
            ->and($post->published_at)->toBeNull();
    });

    it('refuses to unpublish a draft', function (): void {
        $post = Post::factory()->create();

        expect(fn (): Post => resolve(UnpublishPost::class)->handle($post))
            ->toThrow(PostNotPublishedException::class, "Post {$post->id} is not published.");
    });
});
