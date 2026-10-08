---
name: fix-failing-check
description: Diagnose and fix a failing `composer check` (Pint, Rector, Prettier, PHPStan, Pest, arch tests, type coverage) without weakening the rules. Use whenever a quality gate is red.
---

# Fix a failing check

`composer check` = `lint` (Pint, Rector dry-run, Prettier) → `analyse` (PHPStan max) → `test` (Pest) → `test:types` (100%). Output is JSON when run by an agent; read `error_details`.

## Order of operations
0. Failures can print huge HTML bodies. Re-run only the failing test (`./vendor/bin/pest --filter="…"`) and read the `message` field, not the whole response dump.
1. **Style first**: `composer fix` (Rector → Pint → Prettier). Re-run `composer check`. Never hand-format.
2. **PHPStan** (`composer analyse`): fix the cause, not the symptom.
   - `mixed` from config/request → typed accessors: `$request->string('x')->toString()`, `->integer()`, `->boolean()`, `->array()`, `Config::string()`.
   - Missing generics → `@return BelongsTo<User, $this>`, `@return LengthAwarePaginator<int, Post>`, `@extends Factory<Post>`, `@use HasFactory<PostFactory>`.
   - Model properties → `@property` PHPDoc on the model.
   - `starter.eloquentInController` → move the query into a Query object or the write into an Action.
   - **Forbidden**: baseline entries, `@phpstan-ignore`, lowering the level, `@var` casts to silence errors.
3. **Arch tests** (`tests/Arch`): the message names the rule. Typical fixes: make class `final`/`readonly`, add `Data`/`Query`/`Controller` suffix, remove a Facade from a domain class (inject the contract), stop importing another module's internals (use its Contract/Event), translate hard-coded Blade text, replace `ml-4` with `ms-4`, add the missing `fa`/`en` key.
4. **Pest failures**: run the single test (`./vendor/bin/pest --filter="name"`); read the actual vs expected; fix code if behaviour is wrong, fix the test only if the spec changed. Lazy-loading exceptions → eager-load in the Query.
5. **Type coverage < 100%**: `php -d memory_limit=1G vendor/bin/pest --type-coverage --min=100` lists files; add param/return/property types (closures in tests too).
6. **Prettier**: `npm run format`.

## Never
- Disable a rule, skip a test, or add `->skip()` to get green (unless documenting a known bug the human must decide on — then say so explicitly).
- Edit `vendor/`, `.env`, or committed migrations.

Re-run the full `composer check` at the end; report what failed, the root cause, and the fix.
