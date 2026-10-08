<?php

declare(strict_types=1);

namespace App\Modules\Blog\Exceptions;

use App\Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class PostNotPublishedException extends DomainException
{
    public function errorCode(): string
    {
        return 'blog.post_not_published';
    }

    public function translationKey(): string
    {
        return 'blog::posts.errors.not_published';
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
