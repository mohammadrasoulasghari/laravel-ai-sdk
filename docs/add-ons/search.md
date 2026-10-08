# Search

## When to use

Full-text, typo-tolerant or ranked search across many records. For small admin tables, a `filter[search]` in a Query object (like `app/Modules/Blog/Queries/AdminPostsQuery.php`) is enough.

## Recommended package

**`laravel/scout`** with an engine: `database` (MySQL full-text, no extra service), **Meilisearch** or **Typesense** (fast, typo-tolerant; good Persian support — test with your data). Verify versions with the `researcher` agent; add the engine as a service in `compose.yaml` if you choose one.

## Module to change

Search belongs to the module that owns the data (e.g. Blog posts). A cross-module "global search" gets its own module (`php artisan make:module Search`) that listens to other modules' events and never imports their models.

## Integration points

- **Model** — add `Searchable` and `toSearchableArray()` with explicit fields (no secrets, no unpublished drafts — use `shouldBeSearchable()`).
- **Query object** — `SearchPostsQuery` wraps `Post::search($term)` and eager-loads what the view needs (strict mode). Controllers still call only the Query.
- **API** — reuse the listing contract (`filter[search]`, `per_page`) from [`docs/api.md`](../api.md).
- **Indexing** — `SCOUT_QUEUE=true` so indexing runs on Horizon; `php artisan scout:import "App\Modules\Blog\Models\Post"` after deploy.
- **Persian text** — normalise Arabic/Persian variants (ي/ی, ك/ک) and digits before indexing and searching (a small value object or helper).
- **Config** — `config/scout.php`; `.env.example`: `SCOUT_DRIVER=database`, `MEILISEARCH_HOST=`, `MEILISEARCH_KEY=`.

## Tests to add

- `SCOUT_DRIVER=collection` (or `database`) in `phpunit.xml` so tests need no search service.
- Matching, non-matching and unpublished records; Persian normalisation (ی vs ي).
- Route tests for the search endpoint/page (empty term, long term validation, pagination).
