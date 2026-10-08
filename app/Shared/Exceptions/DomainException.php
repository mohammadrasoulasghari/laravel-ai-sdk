<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base for every business-rule violation. Throw a specific subclass, never this directly.
 *
 * - getMessage() is an English, developer-facing message (logs).
 * - translationKey()/translationParameters() produce the user-facing text.
 * - errorCode() becomes the RFC 9457 "type" (urn:problem:<code>) for API clients.
 */
abstract class DomainException extends RuntimeException
{
    /** Stable machine-readable code, e.g. "blog.post_already_published". */
    abstract public function errorCode(): string;

    /** Translation key for the user-facing message, e.g. "blog::exceptions.post_already_published". */
    abstract public function translationKey(): string;

    /** @return array<string, string|int> */
    public function translationParameters(): array
    {
        return [];
    }

    /** @return array<string, string> Extra response headers, e.g. ['Retry-After' => '60']. */
    public function headers(): array
    {
        return [];
    }

    public function status(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
