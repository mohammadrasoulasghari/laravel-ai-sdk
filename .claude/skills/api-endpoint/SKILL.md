---
name: api-endpoint
description: Add a versioned /api/v1 endpoint that reuses the module's Actions/Queries, returns API Resources, errors as RFC 9457 problem+json, and appears in the Scramble OpenAPI docs.
---

# API endpoint

Reference: `app/Modules/Blog/Http/Controllers/Api/PostController.php`, `Http/Resources/PostResource.php`, `Http/Requests/PostIndexRequest.php`, `routes/api.php`, `docs/api.md`.

## Steps
1. Route in the module's `routes/api.php` (already prefixed `/api/v1`, named `api.v1.`):
   ```php
   Route::prefix('blog')->name('blog.')->group(function (): void {
       Route::get('posts', [PostController::class, 'index'])->name('posts.index');
       Route::middleware('auth:sanctum')->post('posts', [PostController::class, 'store'])->name('posts.store');
   });
   ```
   Bind by id for writes: `{post:id}`.
2. Controller in `Http/Controllers/Api/`: reuse the **same** Action/Query as the web controller. Return `new XResource(...)`, `XResource::collection($paginator)`, or `response()->noContent()`.
3. Listing input: a FormRequest like `PostIndexRequest` implementing the contract `filter[...]`, `sort` (backed enum), `per_page` (max `starter.pagination.max_per_page`). Unknown filters must fail validation (`'filter' => ['array:search,category']`).
4. Resource: explicit fields only, ISO-8601 dates, `whenLoaded()` for relations. Never expose secrets.
5. Docs: give each controller method a one-line PHPDoc summary; add `@unauthenticated` for public endpoints. Scramble renders `/docs/api`. Update the endpoint table in `docs/api.md`.
6. Errors need no code: validation → 422 problem+json with `errors`; `DomainException` → its status + `urn:problem:<code>`; policy → 403.
7. Tests (`Sanctum::actingAs($user)`): 200/201 shape (`assertJsonStructure`), 401, 403, 422 with `application/problem+json`, domain error `type`.

## Checklist
- [ ] Same Action/Query as web; no duplicated logic
- [ ] Policy enforced (`Gate::authorize` or FormRequest `authorize`)
- [ ] Resource fields explicit; docs/api.md updated
- [ ] Tests for success, 401, 403, 422, domain error
