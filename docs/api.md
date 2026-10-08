# API contract

Versioned JSON API next to the web app. Same Actions and Queries as the web controllers —
only the output layer (API Resources, problem+json) differs. Decision: ADR-0010.

- Base URL: `/api/v1`
- Interactive docs (OpenAPI 3.1, generated from code by Scramble): **`/docs/api`**, raw spec at `/docs/api.json`.
  Available in `local`; elsewhere requires the `system.tools.view` permission.

## Authentication

Sanctum personal access tokens (Bearer).

```http
POST /api/v1/auth/tokens
Content-Type: application/json
Accept: application/json

{"email": "admin@example.com", "password": "password", "device_name": "my-cli"}
```

```json
201 Created
{"data": {"token": "1|Xyz…", "token_type": "Bearer", "expires_at": "2026-11-07T10:00:00+00:00"}}
```

Use it: `Authorization: Bearer 1|Xyz…`. Revoke the current token: `DELETE /api/v1/auth/tokens/current`.
Token lifetime: `SANCTUM_TOKEN_EXPIRATION` minutes (default 30 days). Changing password revokes all tokens.

## Rate limits

| Limiter | Applies to | Limit |
|---|---|---|
| `api` | every `/api/*` route | 60 / minute per user (or IP when anonymous) |
| `auth` | token issuing, login, register, password reset | 10 / minute per IP, plus 5 failed logins per email+IP |

Exceeding a limit returns `429` problem+json with `Retry-After`.

## Request conventions

- Always send `Accept: application/json`.
- Locale for messages: `Accept-Language: fa` or `en` (response has `Content-Language`).
- Correlation: send `X-Request-Id` (8–64 chars `[A-Za-z0-9-_.]`) or one is generated; it is echoed back and appears in logs and error bodies.

### Listing endpoints (pagination, filtering, sorting)

| Parameter | Example | Notes |
|---|---|---|
| `page` | `?page=2` | 1-based |
| `per_page` | `?per_page=50` | default 15, max 100 (`config/starter.php`) |
| `filter[<field>]` | `?filter[category]=news&filter[search]=laravel` | only fields documented per endpoint; unknown filters → 422 |
| `sort` | `?sort=-published_at` | one field; `-` prefix = descending; allowed values per endpoint (enum) |

Response shape (Laravel API Resource collection):

```json
{
  "data": [ { "id": "01J…", "title": "…" } ],
  "links": { "first": "…", "last": "…", "prev": null, "next": "…" },
  "meta": { "current_page": 1, "last_page": 4, "per_page": 15, "total": 52, "from": 1, "to": 15, "path": "…" }
}
```

Single resources: `{"data": {…}}`. Deletes: `204 No Content`.

## Errors — RFC 9457 problem details

Every error is `Content-Type: application/problem+json`:

```json
{
  "type": "urn:problem:blog.post_already_published",
  "title": "Conflict",
  "status": 409,
  "detail": "This post is already published.",
  "instance": "/api/v1/blog/posts/01J…/publication",
  "correlation_id": "0192f1c2-…"
}
```

| Status | `type` | When |
|---|---|---|
| 401 | `about:blank` | missing/invalid token |
| 403 | `about:blank` | policy denied |
| 404 | `about:blank` | unknown route or record |
| 409/422/… | `urn:problem:<module>.<code>` | business rule (`DomainException`) |
| 422 | `about:blank` + `errors` | validation: `"errors": {"title": ["The title field is required."]}` |
| 429 | `about:blank` | rate limited |
| 500 | `about:blank` | unexpected; `detail` only when `APP_DEBUG=true` |

`title` and `detail` are translated to the request locale. Clients should branch on `status` and `type`, never on text.

## Endpoints (built-in)

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/auth/tokens` | – | Issue token |
| DELETE | `/auth/tokens/current` | token | Revoke current token |
| GET | `/me` | token | Current user with roles |
| GET | `/blog/posts` | – | Published posts. Filters: `search`, `category` (slug). Sort: `published_at`, `-published_at`, `title`, `-title` |
| GET | `/blog/posts/{slug}` | – | One published post |
| POST | `/blog/posts` | `blog.posts.manage` | Create draft |
| PUT/PATCH | `/blog/posts/{id}` | `blog.posts.manage` | Update |
| DELETE | `/blog/posts/{id}` | `blog.posts.manage` | Delete |
| POST | `/blog/posts/{id}/publication` | `blog.posts.manage` | Publish |
| DELETE | `/blog/posts/{id}/publication` | `blog.posts.manage` | Unpublish |

## Adding an endpoint

Follow the `api-endpoint` skill (`.claude/skills/api-endpoint/SKILL.md`): route in the module's `routes/api.php`,
thin controller in `Http/Controllers/Api`, reuse the module's Action/Query, return a Resource, add a PHPDoc summary
(Scramble uses it), and feature-test the success, 401/403, 422 and problem+json paths.

## Versioning policy

Breaking changes (removing/renaming fields, changing types or semantics) require `/api/v2` routes. Additive changes
(new optional fields, new endpoints) are allowed in v1. Deprecate with a `Deprecation` header and a CHANGELOG entry
at least one minor release before removal.
