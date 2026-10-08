<?php

declare(strict_types=1);

it('assigns a correlation id to every response', function (): void {
    $id = $this->get('/up')->headers->get('X-Request-Id');

    expect($id)->toBeString()->toHaveLength(36);
});

it('reuses a safe incoming request id', function (): void {
    $this->get('/up', ['X-Request-Id' => 'abc-12345678'])->assertHeader('X-Request-Id', 'abc-12345678');
});

it('replaces an unsafe incoming request id', function (): void {
    $id = $this->get('/up', ['X-Request-Id' => "bad\nheader<script>"])->headers->get('X-Request-Id');

    expect($id)->not->toContain('script');
});
