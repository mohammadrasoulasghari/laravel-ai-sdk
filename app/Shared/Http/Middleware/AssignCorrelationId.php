<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every request a correlation id (reusing a safe incoming X-Request-Id),
 * shares it with logs/queued jobs through Laravel Context, and echoes it back.
 */
final readonly class AssignCorrelationId
{
    public const string HEADER = 'X-Request-Id';

    public const string CONTEXT_KEY = 'correlation_id';

    public function __construct(private Context $context) {}

    /** @param  Closure(Request): Response  $next */
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->headers->get(self::HEADER);
        $id = is_string($incoming) && preg_match('/^[A-Za-z0-9\-_.]{8,64}$/', $incoming) === 1
            ? $incoming
            : (string) Str::uuid7();

        $this->context->add(self::CONTEXT_KEY, $id);

        $response = $next($request);
        $response->headers->set(self::HEADER, $id);

        return $response;
    }
}
