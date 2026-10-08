<?php

declare(strict_types=1);

namespace App\Modules\Blog\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class CategoryHasPostsException extends DomainException
{
    public function __construct(public readonly int $postCount)
    {
        parent::__construct("Category still has {$postCount} posts.");
    }

    public function errorCode(): string
    {
        return 'blog.category_has_posts';
    }

    public function translationKey(): string
    {
        return 'blog::categories.errors.has_posts';
    }

    public function translationParameters(): array
    {
        return ['count' => $this->postCount];
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
