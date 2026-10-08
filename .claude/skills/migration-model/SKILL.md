---
name: migration-model
description: Create a reversible migration, an Eloquent model (relations/casts/scopes only), a factory and optional seeder inside a module, following the database conventions (ULIDs, FKs, indexes).
---

# Migration + model

Reference: `app/Modules/Blog/Database/Migrations/*`, `Models/Post.php`, `Models/Category.php`, `Database/Factories/PostFactory.php`, `ValueObjects/Slug.php` (castable VO). Conventions §5, ADR-0007.

## Steps
1. Migration inside the module:
   `php artisan make:migration create_contact_messages_table --path=app/Modules/Contact/Database/Migrations`
   - Table name prefixed by module (`contact_messages`).
   - `$table->ulid('id')->primary();` · `$table->foreignUlid('user_id')->constrained()->cascadeOnDelete();` (choose cascade/restrict/nullOnDelete deliberately).
   - Index columns used for filtering/sorting; composite index for `(status, created_at)`-style queries.
   - Docblock: soft-delete decision and why.
   - `down()` drops exactly what `up()` created. Never edit a committed migration; add a new one.
2. Model `Models/ContactMessage.php`:
   ```php
   #[Table('contact_messages')]
   #[Fillable(['name', 'email', 'message'])]
   #[UseFactory(ContactMessageFactory::class)]
   final class ContactMessage extends Model
   {
       /** @use HasFactory<ContactMessageFactory> */
       use HasFactory;
       use HasUlids;
       // relations with generics: /** @return BelongsTo<User, $this> */
       // #[Scope] protected function unread(Builder $query): void { … }
       protected function casts(): array { return ['status' => MessageStatus::class, 'read_at' => 'immutable_datetime']; }
   }
   ```
   Add `@property` PHPDoc for every column (PHPStan max needs them). **No business methods** — arch test `ModelsTest`.
   Status columns are backed enums; state-changing fields stay out of `#[Fillable]` and are set by Actions.
3. Factory `Database/Factories/ContactMessageFactory.php` (`final`, `@extends Factory<ContactMessage>`, `protected $model = …`), with states for each status.
4. Seeder only for demo data, local-only guard (`if (! app()->environment('local')) return;`).
5. `php artisan migrate` then `php artisan migrate:rollback --step=1 && php artisan migrate` to prove `down()`.

## Checklist
- [ ] ULID PK, explicit FK behaviour, indexes
- [ ] Reversible; not editing committed migrations
- [ ] Model: fillable, casts, relations with generics, `@property` docs, no logic
- [ ] Factory with states
