# Conventions

The rules every contributor — human or AI — follows. Each rule names its **enforcer**;
if a rule has no enforcer, a reviewer (or the `code-reviewer` agent) checks it.

Reference implementation for everything below: `app/Modules/Blog`.

---

## 1. PHP basics

| Rule | Enforcer |
|---|---|
| `declare(strict_types=1);` in every PHP file | Pint `declare_strict_types`, arch test |
| Classes are `final` (abstract bases excepted) | Pint `final_class`, arch test |
| DTOs and Value Objects are `final readonly` | arch test |
| Full type coverage (params, returns, properties; generics via PHPDoc) | `pest --type-coverage --min=100`, PHPStan max |
| No `dd`, `dump`, `ray`, `var_dump`, `die`, `exit` | arch test |
| `env()` only in `config/*.php` | arch test |
| Strict comparisons (`===`) | Pint `strict_comparison` |
| No magic strings for statuses/roles/permissions/settings → backed enums | review |
| No empty `catch`; never swallow exceptions | review |

## 2. Layers and where code goes

```
Request → FormRequest (validate + authorize, ->toData())
        → Controller (thin)  → Action::handle(Data)  → Model / Event
                             → Query::handle(...)    → paginator / collection
        → View (Blade, UI kit) or API Resource
```

| Layer | Folder | Rules |
|---|---|---|
| Controller | `Http/Controllers` | `final`, suffix `Controller`. Only: authorize, call one Action or Query, return view/resource/redirect. **No Eloquent calls** (no `Model::query()`, `->save()`, `->where()` …); route-model binding and `->load()` are allowed. Enforced by PHPStan rule `starter.eloquentInController` + arch test. |
| Form request | `Http/Requests` | suffix `Request`, owns `rules()` + `authorize()` and a `toData(): XxxData` method. |
| Action | `Actions` | `final readonly`, verb-noun name **without** `Action` suffix (`PublishPost`), exactly one public `handle()` method, receives DTOs/models, returns a model/DTO/void. Wraps multi-write work in `$this->db->transaction()`. Throws domain exceptions. |
| DTO | `Data` | `final readonly`, suffix `Data`, constructor-promoted public properties, no logic beyond tiny derived getters. |
| Query object | `Queries` | `final readonly`, suffix `Query`, one `handle()` returning a Builder/paginator/collection. Eager-loads everything the view needs. |
| Model | `Models` | relations, casts, scopes (`#[Scope]`), accessors only. `#[Fillable]` explicit. **No business methods** (arch test inspects public methods). |
| Value object | `ValueObjects` | `final readonly`, validated in the constructor, equality via `equals()`. |
| Event | `Events` | past tense (`PostPublished`), `final readonly`, carries IDs/DTOs not request objects. |
| Listener / Job | `Listeners`, `Jobs` | Jobs implement `ShouldQueue`, are idempotent (check state before acting), and set `$tries`/`backoff()`. |
| Policy | `Policies` | suffix `Policy`, checks permissions via enum values. |
| Exception | `Exceptions` | extends `App\Shared\Exceptions\DomainException`, suffix `Exception`, provides `errorCode()` + `translationKey()`. |

Domain layers (`Actions`, `Data`, `Enums`, `Events`, `Models`, `Queries`, `ValueObjects`) **never use Facades** — inject contracts (`ConnectionInterface`, `Dispatcher`, `Cache\Repository` …). Facades are fine in controllers, providers, middleware and routes. *(arch test)*

## 3. Modules

- One business domain per module in `app/Modules/<Name>`; create with `php artisan make:module <Name>`.
- Public API of a module = `Contracts\`, `Events\`, `Data\`, `Enums\`. Everything else is private.
  The only exception is `App\Modules\Users\Models\User`. *(arch test `ModuleBoundariesTest`, ADR-0006)*
- Cross-module **reads/writes** go through a `Contracts\` interface bound in the owning module's provider
  (example: `Users\Contracts\UserRegistrar` used by `Auth\Actions\RegisterUser`).
- Cross-module **reactions** go through events.
- `App\Shared` never depends on a module. *(arch test)*

## 4. Naming

| Thing | Pattern | Example |
|---|---|---|
| Route names | `<area>.<resource>.<action>` | `blog.posts.show`, `admin.blog.posts.edit`, `api.v1.blog.posts.index` |
| View names | `<module-kebab>::<path>` | `blog::posts.show`, `blog::admin.posts.form` |
| Translation keys | `<module-kebab>::<file>.<key>` | `blog::posts.created` |
| Permissions | `<module>.<resource>.<verb>` / `<module>.manage` | `blog.posts.manage` |
| Tables | plural snake | `blog_posts` (prefix module tables with the module name) |
| Migrations | `YYYY_MM_DD_HHMMSS_<verb>_<table>_table.php` in `Database/Migrations` | |

## 5. Database

- ULID primary keys (`$table->ulid('id')->primary()`, `HasUlids`), `foreignUlid()->constrained()->cascadeOnDelete()|restrictOnDelete()` explicitly. *(ADR-0007)*
- Every migration has a working `down()`. Never edit a migration that is already on `main` — add a new one (the `PreToolUse` hook blocks edits to committed migrations).
- Index every foreign key and every column used in `where`/`orderBy` of a Query object.
- Soft deletes only where the business needs restore/audit (Blog posts: no; document the choice in the migration docblock).
- Each model has a factory in `Database/Factories` (registered with `#[UseFactory]`) and, if needed, a seeder called from `database/seeders/DatabaseSeeder.php`.
- `Model::shouldBeStrict()` is on outside production: lazy loading, unknown attributes and silently discarded attributes throw.

## 6. HTTP & API

- Web forms: `@csrf`, `old()` (automatic in UI kit inputs), errors via `<x-ui.form-error>` (automatic), success via `->with(Flash::success(__('...')))`.
- Domain exceptions on web routes redirect back with a flash error automatically; on API routes they become RFC 9457 problem+json.
- API: `/api/v1`, Sanctum bearer tokens, `throttle:api` (60/min) by default; responses through API Resources; errors are problem+json only. Query contract in [`api.md`](api.md).
- Authorization only via Policies/Gates (`Gate::authorize()`, `$request->user()->can()`, `can:` middleware). Never check roles by name in controllers.

## 7. Blade & UI

- Use the UI kit (`<x-ui.*>`) and layouts (`<x-layouts.app|guest|admin>`). Need something new? Add a component (skill `blade-component`) and show it in the style guide (`/_styleguide`).
- **No hard-coded user-visible text**: every string through `__()`; keys in both `lang/en` and `lang/fa`. *(arch tests `BladeTest`, `TranslationsTest`)*
- **Logical direction utilities only**: `ms-/me-/ps-/pe-/start-/end-/text-start/text-end/border-s/border-e/rounded-s/rounded-e`. Never `ml-/mr-/pl-/pr-/left-/right-/text-left/text-right`. *(arch test)*
- Colors only from tokens (`bg-surface`, `text-fg-muted`, `bg-primary`, …) — never raw palette colors like `bg-blue-600`.
- `{!! !!}` only with a `{{-- unescaped: <reason> --}}` comment on the line above. *(arch test)*
- No queries or business logic in views. Views receive eager-loaded models or DTOs; strict mode throws on lazy loading.
- Dates through `<x-ui.date :value="..." />` (Jalali for fa, Gregorian for en).
- a11y: every input has a label (UI kit does it), one `<h1>` per page (layouts render `title`), visible focus (global), `sr-only` text for icon buttons.

## 8. Tests

- Pest only. Mirror module structure: `tests/Feature/Modules/<Module>/…`, `tests/Unit/Modules/<Module>/…`.
- Every Action: a test for the happy path and each exception. Every route: auth, authorization (403), validation (422/redirect with errors), and success.
- Use factories, `Event::fake()`, `Notification::fake()`, `Queue::fake()`; never hit the network.
- Helpers in `tests/Support/helpers.php`: `userWithRole()`, `userWithPermissions()`.

## 9. Good vs bad

```php
// ❌ Bad — controller does everything
public function store(Request $request)
{
    $post = Post::create($request->all());           // mass-assignment of raw input, Eloquent in controller
    if ($request->publish) { $post->published_at = now(); $post->save(); }
    return redirect('/posts')->with('ok', 'Saved!');  // hard-coded string, magic URL
}

// ✅ Good
public function store(StorePostRequest $request, CreatePost $createPost): RedirectResponse
{
    $post = $createPost->handle($request->toData(), $request->user());

    return to_route('admin.blog.posts.edit', $post)->with(Flash::success(__('blog::posts.created')));
}
```

```blade
{{-- ❌ Bad --}}
<div class="ml-4 text-left bg-blue-600">Delete post?</div>
@foreach (App\Modules\Blog\Models\Post::all() as $post) … @endforeach

{{-- ✅ Good --}}
<div class="ms-4 text-start bg-primary">{{ __('blog::posts.delete_confirm') }}</div>
@foreach ($posts as $post) … @endforeach
```

## 10. Commits

Conventional Commits, lowercase summary: `feat(blog): add post scheduling`. Types: feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert. *(CaptainHook `commit-msg`)*
