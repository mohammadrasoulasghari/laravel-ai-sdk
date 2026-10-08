<?php

declare(strict_types=1);

namespace App\Shared\Http\Controllers;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\Factory as Queue;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Readiness probe: can this instance serve traffic right now? (liveness is /up)
 */
final readonly class ReadinessController
{
    public function __construct(
        private ConnectionInterface $db,
        private Cache $cache,
        private Queue $queue,
    ) {}

    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn (): bool => $this->db->select('select 1 as ok') !== []),
            'cache' => $this->check(function (): bool {
                $key = 'health:'.Str::random(8);
                $this->cache->put($key, true, 5);

                return $this->cache->pull($key) === true;
            }),
            'queue' => $this->check(fn (): bool => $this->queue->connection()->size() >= 0),
        ];

        $healthy = ! in_array(false, $checks, true);

        return new JsonResponse(
            ['status' => $healthy ? 'ok' : 'fail', 'checks' => array_map(static fn (bool $ok): string => $ok ? 'ok' : 'fail', $checks)],
            $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }

    /** @param  callable(): bool  $probe */
    private function check(callable $probe): bool
    {
        try {
            return $probe();
        } catch (Throwable $throwable) {
            report($throwable);

            return false;
        }
    }
}
