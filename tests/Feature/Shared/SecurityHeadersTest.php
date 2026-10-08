<?php

declare(strict_types=1);

it('sends baseline security headers', function (): void {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9]+'/");
});

it('uses the same nonce for the inline theme script', function (): void {
    $response = $this->get('/');

    preg_match("/'nonce-([A-Za-z0-9]+)'/", (string) $response->headers->get('Content-Security-Policy'), $match);

    $response->assertSee('nonce="'.$match[1].'"', false);
});

it('can send CSP as report-only', function (): void {
    config(['security.csp_report_only' => true]);

    $response = $this->get('/');

    expect($response->headers->has('Content-Security-Policy-Report-Only'))->toBeTrue()
        ->and($response->headers->has('Content-Security-Policy'))->toBeFalse();
});

it('only sends HSTS over HTTPS when enabled', function (): void {
    config(['security.hsts' => true]);

    $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
    $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('answers CORS preflight only for configured origins', function (): void {
    config(['cors.allowed_origins' => ['https://app.example.com']]);

    $this->options('/api/v1/blog/posts', [], [
        'Origin' => 'https://app.example.com',
        'Access-Control-Request-Method' => 'GET',
    ])->assertHeader('Access-Control-Allow-Origin', 'https://app.example.com');

    $evil = $this->options('/api/v1/blog/posts', [], [
        'Origin' => 'https://evil.example.com',
        'Access-Control-Request-Method' => 'GET',
    ]);

    expect($evil->headers->get('Access-Control-Allow-Origin'))->not->toBe('https://evil.example.com');
});
