# Multi-tenancy

## When to use

One deployment serves several customers (tenants) whose data must never mix — SaaS workspaces,
schools, clinics. Skip it if you deploy one instance per customer; that is simpler and safer.

## Options

| Approach | Isolation | Cost |
|---|---|---|
| Single database, `tenant_id` column + global scope | logical | cheapest; every query must be scoped |
| Database per tenant | physical | migrations per tenant, more ops work |

Recommended package for either: **`stancl/tenancy`** (verify current version and Laravel 13 support with the `researcher` agent). For the single-database approach a hand-rolled scope is also reasonable — record the choice in an ADR.

## Module to create

`php artisan make:module Tenancy` — owns the `Tenant` model, tenant resolution (subdomain, domain or header), `CurrentTenant` contract and tenant administration screens.

## Integration points

- **Resolution middleware** — add to the `web` / `api` groups in `bootstrap/app.php` (`withMiddleware`). Resolve the tenant once per request and store it in a `Contracts\CurrentTenant` service (and Laravel `Context`, so it reaches logs and queued jobs like the correlation id does).
- **Models** — tenant-owned models get a `tenant_id` `foreignUlid()` column (ADR-0007) and a global scope. Add an arch test that every model in tenant-owned modules uses the scope/trait.
- **Queries/Actions** — never pass `tenant_id` from input; take it from `CurrentTenant`.
- **Cache keys** — prefix with the tenant id (`<tenant>:<module>:<thing>`); update [`docs/caching.md`](../caching.md).
- **Permissions** — spatie/laravel-permission supports "teams"; enable it in `config/permission.php` if roles are per tenant.
- **Storage** — prefix upload paths with the tenant id.

## Tests to add

- A user of tenant A gets 404 (not 403) for every route that touches tenant B's records.
- Queued jobs run in the tenant that dispatched them.
- Cache keys do not collide between tenants.
- Arch test: tenant-owned models are scoped.
