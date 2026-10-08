<?php

declare(strict_types=1);

namespace App\Shared\Http\Problems;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * RFC 9457 "Problem Details for HTTP APIs" response.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9457
 */
final readonly class ProblemDetails implements Responsable
{
    public const string CONTENT_TYPE = 'application/problem+json';

    /**
     * @param  array<string, mixed>  $extensions  Extra members (e.g. "errors", "correlation_id").
     */
    public function __construct(
        public int $status,
        public string $title,
        public ?string $detail = null,
        public string $type = 'about:blank',
        public ?string $instance = null,
        public array $extensions = [],
        /** @var array<string, string> HTTP headers to send (e.g. Retry-After, Allow). */
        public array $headers = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->detail,
            'instance' => $this->instance,
            ...$this->extensions,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /** @param  Request  $request */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: $this->toArray(),
            status: $this->status,
            headers: [...$this->headers, 'Content-Type' => self::CONTENT_TYPE],
            options: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
