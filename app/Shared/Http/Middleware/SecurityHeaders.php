<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for every response (OWASP Secure Headers).
 * CSP uses a per-request nonce shared with Vite and the inline theme script.
 * Alpine.js (standard build) evaluates expressions, which requires 'unsafe-eval' — ADR-0015.
 */
final readonly class SecurityHeaders
{
    public function __construct(private Config $config) {}

    /** @param  Closure(Request): Response  $next */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($this->config->boolean('security.hsts') && $request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (! $headers->has('Content-Security-Policy') && ! $this->isExcluded($request)) {
            $name = $this->config->boolean('security.csp_report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $headers->set($name, $this->contentSecurityPolicy());
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $nonce = Vite::cspNonce();
        $devServer = $this->config->string('security.vite_dev_server');

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' {$devServer}",
            "style-src 'self' 'unsafe-inline' {$devServer}",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self' {$devServer} ".str_replace('http', 'ws', $devServer),
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
    }

    /** Vendor dashboards ship their own inline scripts. */
    private function isExcluded(Request $request): bool
    {
        return $request->is('horizon*', 'telescope*', 'pulse*', 'docs/api*');
    }
}
