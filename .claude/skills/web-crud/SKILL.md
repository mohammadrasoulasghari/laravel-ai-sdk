---
name: web-crud
description: Build server-rendered CRUD screens (routes, thin controller, FormRequest with toData, Policy, Blade views from the UI kit, flash messages, translations). Use for admin or public pages.
---

# Web CRUD

Reference: `app/Modules/Blog/Http/Controllers/Admin/PostController.php`, `Http/Requests/PostRequest.php`, `Policies/PostPolicy.php`, `resources/views/admin/posts/{index,form}.blade.php`, `routes/web.php`.

## Steps
1. **Routes** (`routes/web.php` of the module):
   ```php
   Route::middleware('admin')->prefix('admin/blog')->name('admin.blog.')->group(function (): void {
       Route::resource('posts', AdminPostController::class)->except('show');
   });
   ```
   The `admin` group = `auth` + `verified` + `can:admin.access`. Public pages use plain routes (`blog.posts.index`).
2. **Policy** with permission enum checks; register in the provider's `$policies`.
3. **FormRequest**: `authorize()` via policy (`$this->user()?->can('update', $post)`), `rules()`, and `toData()` returning the DTO. One request for create+update is fine when rules match (ignore unique on update).
4. **Controller** (`final`, suffix `Controller`): `Gate::authorize()` for GET/DELETE, FormRequest for POST/PUT; call **one** Action/Query; return a view or `to_route(...)->with(Flash::success(__('...')))`. No Eloquent calls (PHPStan rule).
5. **Read side**: a Query object that eager-loads what the view needs (strict mode throws on lazy loading).
6. **Views**: `<x-layouts.admin :title="__('...')">`, forms with `<x-ui.input|select|textarea|checkbox>` (they handle label, `old()`, errors), tables with `<x-ui.table>` + `<x-ui.pagination>`, destructive actions behind `<x-ui.modal>`. Use `blade-component` if you need a new component.
7. **Navigation** item in the provider (`NavigationArea::Admin`, ability = permission).
8. **Translations** in fa + en (`i18n-rtl` skill).
9. **Tests**: guest → redirect `login`; member → 403; validation errors; success (DB + flash); delete.

## Checklist
- [ ] Every route behind correct middleware; every action authorized
- [ ] Controller methods are ≤ ~10 lines
- [ ] Views use only UI kit + logical classes + `__()`
- [ ] Tests for 302/403/422-ish/200 paths
