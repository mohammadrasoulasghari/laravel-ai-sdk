# Security policy

## Supported versions

Security fixes are released for the latest minor version of the current major.

| Version | Supported |
|---|---|
| 1.x (latest) | ✅ |

## Reporting a vulnerability

**Please do not open a public issue for security problems.**

Report privately through GitHub: **Security → Advisories → "Report a vulnerability"** on this repository.
Include what you found, how to reproduce it, the affected version/commit and, if possible, a suggested fix.

You can expect an acknowledgement within a few days and a status update as we investigate. We will credit reporters in
the release notes unless they prefer to stay anonymous.

## Scope

In scope: code and configuration in this repository (authentication, authorization, CSP/headers, generators, hooks,
the example modules). Out of scope: vulnerabilities in third-party packages (report them upstream; we track advisories
with `composer audit` and `npm audit` in CI) and misconfiguration of your own deployment.

Production checklist, in short:
`APP_DEBUG=false`, set `TRUSTED_PROXIES` behind a load balancer, use a strong `ADMIN_PASSWORD`, serve over HTTPS and
enable `SECURITY_HSTS`.
