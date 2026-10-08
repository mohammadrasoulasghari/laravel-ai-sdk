<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Repository;
use Mockery\MockInterface;

it('reports liveness', function (): void {
    $this->get('/up')->assertOk();
});

it('reports readiness with every check', function (): void {
    $this->getJson('/health/ready')
        ->assertOk()
        ->assertExactJson(['status' => 'ok', 'checks' => ['database' => 'ok', 'cache' => 'ok', 'queue' => 'ok']]);
});

it('returns 503 when a dependency fails', function (): void {
    $this->mock(Repository::class, function (MockInterface $cache): void {
        $cache->shouldReceive('put')->andThrow(new RuntimeException('redis down'));
    });

    $this->getJson('/health/ready')
        ->assertStatus(503)
        ->assertJsonPath('status', 'fail')
        ->assertJsonPath('checks.cache', 'fail');
});

it('does not start a session for health checks', function (): void {
    $this->get('/health/ready')->assertCookieMissing(config('session.cookie'));
});
