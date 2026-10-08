<?php

declare(strict_types=1);

use App\Modules\Blog\Exceptions\CategoryHasPostsException;
use App\Modules\Blog\Exceptions\PostAlreadyPublishedException;
use App\Modules\Blog\Exceptions\PostNotPublishedException;
use App\Shared\Exceptions\DomainException;

it('describes each blog domain exception', function (DomainException $exception, string $code, string $message): void {
    expect($exception->errorCode())->toBe($code)
        ->and($exception->status())->toBe(409)
        ->and(__($exception->translationKey(), $exception->translationParameters()))->toBe($message)
        ->and(__($exception->translationKey(), $exception->translationParameters(), 'fa'))
        ->not->toBe($message)
        ->not->toBe($exception->translationKey());
})->with([
    'post already published' => [
        new PostAlreadyPublishedException('Post x is already published.'),
        'blog.post_already_published',
        'This post is already published.',
    ],
    'post not published' => [
        new PostNotPublishedException('Post x is not published.'),
        'blog.post_not_published',
        'This post is not published.',
    ],
    'category has posts' => [
        new CategoryHasPostsException(3),
        'blog.category_has_posts',
        'This category still has 3 posts. Move or delete them first.',
    ],
]);

it('keeps the post count on CategoryHasPostsException', function (): void {
    $exception = new CategoryHasPostsException(2);

    expect($exception->postCount)->toBe(2)
        ->and($exception->getMessage())->toBe('Category still has 2 posts.')
        ->and($exception->translationParameters())->toBe(['count' => 2]);
});
