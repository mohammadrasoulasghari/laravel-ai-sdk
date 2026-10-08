# Caching strategy

Cache store: Redis (`CACHE_STORE=redis`); tests use `array`. Prefix: `CACHE_PREFIX` (defaults to the app slug).

Rules:

1. Cache only **reads that are expensive or very frequent and change rarely**. Never cache per-user authorization decisions yourself (spatie caches permissions).
2. Every key is a **constant** next to the code that reads it, named `<module>:<thing>[:<id>]`.
3. Every write path that changes the source **forgets** the key in the same Action (after the transaction).
4. Prefer `rememberForever` + explicit invalidation over TTL guesswork; use TTLs only for data you cannot invalidate (external APIs).
5. Cached values are arrays/scalars/DTOs, never Eloquent models (serialization breaks with strict mode and schema changes).

| Key | Owner | Contents | TTL | Invalidated by |
|---|---|---|---|---|
| `settings:all` | `Settings\Queries\SettingsQuery::CACHE_KEY` | all settings as `key => value` | forever | `Settings\Actions\UpdateSettings` |
| `spatie.permission.cache` | spatie/laravel-permission | roles & permissions map | 24h | package on role/permission change; `SyncPermissions` |
| `laravel_cache:…` rate limiter keys | framework | throttle counters | per limiter | automatic |
| Pennant `features` | Laravel Pennant (database driver + in-memory per request) | resolved feature flags | per request | `Feature::activate/deactivate` |

Production also caches framework metadata at deploy: `php artisan optimize` (config, routes, views, events).
Remember: after `config:cache`, `env()` returns null outside `config/` — which is why it is banned there by arch tests.

## Adding a cached read

```php
final readonly class PopularPostsQuery
{
    public const string CACHE_KEY = 'blog:popular-posts';

    public function __construct(private Cache $cache) {}

    /** @return list<array{id: string, title: string}> */
    public function handle(): array
    {
        return $this->cache->rememberForever(self::CACHE_KEY, fn (): array => /* query → plain arrays */);
    }
}
// …and in every Action that changes the inputs: $this->cache->forget(PopularPostsQuery::CACHE_KEY);
```

Add a row to the table above in the same PR.
