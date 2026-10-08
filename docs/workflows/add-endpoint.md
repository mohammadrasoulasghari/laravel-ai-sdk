# Add an API endpoint

Endpoints live under `/api/v1`, reuse the same Actions/Queries as the web controllers, return API
Resources and fail with RFC 9457 problem+json automatically.

Full recipe: [`.claude/skills/api-endpoint/SKILL.md`](../../.claude/skills/api-endpoint/SKILL.md) · Reference: `app/Modules/Blog/Http/Controllers/Api/PostController.php` · Contract: [`docs/api.md`](../api.md) · ADR-0010.

## Steps

1. Make sure the write path exists as an Action (or the read path as a Query):
   ```bash
   php artisan make:action Blog ArchivePost
   php artisan make:query Blog ArchivedPosts
   ```
2. Add the route to the module's `routes/api.php` (already prefixed `/api/v1` and named `api.v1.`); protect writes with `auth:sanctum`.
3. Add a FormRequest with `authorize()`, `rules()` and `toData()`; for listings follow the `filter[...]`, `sort`, `per_page` contract.
4. Add a controller method in `Http/Controllers/Api/` (one-line PHPDoc summary; `@unauthenticated` for public endpoints) and an API Resource with explicit fields.
5. Write tests: success shape, 401, 403, 422 problem+json, domain error `type` → [add-test](add-test.md).
6. Update the endpoint table in [`docs/api.md`](../api.md) and add a line to `CHANGELOG.md` under `[Unreleased]`.
7. Check the generated OpenAPI docs at `http://localhost/docs/api`, then:
   ```bash
   php artisan route:list --path=api/v1
   composer check
   ```
