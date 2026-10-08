<?php

declare(strict_types=1);

/*
| HTTP security headers (App\Shared\Http\Middleware\SecurityHeaders). See ADR-0015.
*/
return [
    // Strict-Transport-Security; enable only when the whole domain is served over HTTPS.
    'hsts' => (bool) env('SECURITY_HSTS', false),

    // Send CSP as report-only while tightening a policy.
    'csp_report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', false),

    // Vite dev server origin allowed by CSP while running `npm run dev`.
    'vite_dev_server' => env('SECURITY_VITE_DEV_SERVER') ?: (env('APP_ENV') === 'local' ? 'http://localhost:5173' : ''),
];
