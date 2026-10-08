---
name: new-action
description: Add a write use case as an Action class with its immutable DTO, domain exceptions and tests. Use for any create/update/delete/state-change business operation.
---

# New action

Reference: `app/Modules/Blog/Actions/PublishPost.php`, `CreatePost.php`, `Data/PostData.php`, `Exceptions/PostAlreadyPublishedException.php`.

## Steps
1. `php artisan make:action Blog ArchivePost` → `Actions/ArchivePost.php`, `Data/ArchivePostData.php` (unless `--data=ExistingData`), `tests/Feature/Modules/Blog/Actions/ArchivePostTest.php` (a `todo` test to fill).
   Model-only actions (delete, mark read, publish) take the model instead of a DTO: `php artisan make:action Blog ArchivePost --no-data`, then type the parameter (`handle(Post $post)`).
2. Name: verb-noun, **no** `Action` suffix. One public method: `handle()`.
3. DTO: `final readonly`, constructor-promoted, typed; secrets get `#[\SensitiveParameter]`. Build it in the FormRequest: `public function toData(): ArchivePostData`.
4. Dependencies via constructor injection of **contracts** (no Facades): `ConnectionInterface $db`, `Dispatcher $events`, `Cache\Repository`, other actions, other modules' `Contracts\*`.
5. Business rule violated? Throw a dedicated exception:
   ```php
   final class PostArchivedException extends DomainException {
       public function errorCode(): string { return 'blog.post_archived'; }
       public function translationKey(): string { return 'blog::posts.errors.archived'; }
       public function status(): int { return Response::HTTP_CONFLICT; }
   }
   ```
   Add the translation key to `lang/en` **and** `lang/fa`. Web routes turn it into a flash error; API routes into problem+json — automatically.
6. Multiple writes → `$this->db->transaction(fn () => …)`. Dispatch events **after** the transaction (see `PublishPost`).
7. Return the model/DTO the caller needs (or `void`).
8. Tests: happy path, each exception, events dispatched (`Event::fake([PostPublished::class])`).

## Checklist
- [ ] `final readonly class`, single `handle()`
- [ ] No Facades / `request()` / `auth()` inside the action — pass what you need in
- [ ] Exceptions extend `App\Shared\Exceptions\DomainException`, translated in fa+en
- [ ] Feature test covers success + every exception
