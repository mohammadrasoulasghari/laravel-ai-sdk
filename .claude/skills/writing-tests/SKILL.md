---
name: writing-tests
description: Write Pest tests (unit, feature, arch) the way this project does — structure, helpers, fakes, what to cover per Action/route, and keeping 100% type coverage.
---

# Writing tests

Reference: `tests/Feature/Modules/Blog/**`, `tests/Unit/Modules/Blog/**`, `tests/Arch/*`, `tests/Support/helpers.php`.

## Layout
- `tests/Unit/Modules/<M>/` — no database (value objects, enums, DTOs, exceptions, pure services). Extends `Tests\TestCase` (app booted) but **no** `RefreshDatabase`.
- `tests/Feature/Modules/<M>/` — HTTP + DB; `LazilyRefreshDatabase` is applied automatically; roles/permissions are seeded (`TestCase::$seeder = RolesSeeder`).
- `tests/Feature/Modules/<M>/Actions/` — actions against the DB.
- `tests/Arch/` — architecture rules (add a rule here when you add a convention).

## Style
```php
<?php

declare(strict_types=1);

use App\Modules\Blog\Models\Post;

use function Tests\userWithRole;

it('publishes a draft', function (): void {
    $admin = userWithRole('admin');
    $post = Post::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.blog.posts.publish', $post))
        ->assertRedirect()
        ->assertSessionHas('flash.type', 'success');

    expect($post->refresh()->status)->toBe(PostStatus::Published);
});
```
- `it('does something observable')`; one behaviour per test; datasets for variants (`->with([...])`).
- Every closure typed (`function (): void`, `fn (): array => …`) — `composer test:types` requires 100%.
- Helpers: `Tests\userWithRole('admin')`, `Tests\userWithPermissions('blog.posts.manage')`.
- API: `Sanctum::actingAs($user)`, `->getJson()`, assert `application/problem+json` + `type` for errors.
- Fakes: `Event::fake([...])`, `Notification::fake()`, `Queue::fake()`, `Mail::fake()`, `Http::fake()`, `Storage::fake('public')`. Never hit the network.
- Time: `$this->travelTo(...)` / `CarbonImmutable::setTestNow()`.

## Coverage expectations
- Action: success + each exception + events.
- Route: guest (302 to login / 401), forbidden (403), invalid input (errors), success (state + response).
- View: renders in `fa` (RTL) at least once per module.

## Run
`composer test` (or `./vendor/bin/pest --filter=Publish`), `composer test:types`, `composer test:coverage` (needs pcov), `composer test:mutate` for Actions/VOs.
