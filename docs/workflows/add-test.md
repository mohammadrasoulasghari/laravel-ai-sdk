# Add tests

Pest only. Tests mirror the module structure; 100% type coverage is required.

Full recipe: [`.claude/skills/writing-tests/SKILL.md`](../../.claude/skills/writing-tests/SKILL.md) · Reference: `tests/Feature/Modules/Blog/`, `tests/Unit/Modules/Blog/`, `tests/Arch/` · Helpers: `tests/Support/helpers.php`.

## Where

| Kind | Folder | Database |
|---|---|---|
| Unit (value objects, enums, DTOs) | `tests/Unit/Modules/<Module>/` | no |
| Action | `tests/Feature/Modules/<Module>/Actions/` | yes (`LazilyRefreshDatabase`, roles seeded) |
| HTTP (web + API) | `tests/Feature/Modules/<Module>/` | yes |
| Architecture rule | `tests/Arch/` | no |

## What to cover

- Every Action: success, each domain exception, dispatched events.
- Every route: guest (redirect to login / 401), forbidden (403), invalid input (errors / 422 problem+json), success.
- Each module renders at least one view in `fa` (RTL).

## Steps

1. Generators create test skeletons for you: `php artisan make:action` (feature test) and `php artisan make:value-object` (unit test).
2. Use `Tests\userWithRole('admin')` / `Tests\userWithPermissions('blog.posts.manage')`, factories and fakes (`Event::fake()`, `Queue::fake()`, `Notification::fake()`, `Http::fake()`, `Storage::fake()`); never hit the network.
3. Type every closure: `function (): void { … }`.
4. Run:
   ```bash
   ./vendor/bin/pest --filter=ArchivePost   # one test
   composer test                            # whole suite
   composer test:types                      # 100% type coverage
   composer test:coverage                   # min 80% (needs pcov or xdebug)
   composer test:mutate                     # mutation testing
   composer check                           # everything CI runs
   ```
