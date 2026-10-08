# Add a Blade page

Server-rendered pages use the layouts (`<x-layouts.app|guest|admin>`) and the UI kit (`<x-ui.*>`),
logical direction classes, token colors and translated text only.

Full recipes: [`.claude/skills/web-crud/SKILL.md`](../../.claude/skills/web-crud/SKILL.md), [`.claude/skills/blade-component/SKILL.md`](../../.claude/skills/blade-component/SKILL.md), [`.claude/skills/i18n-rtl/SKILL.md`](../../.claude/skills/i18n-rtl/SKILL.md) · Reference: `app/Modules/Blog/resources/views/admin/posts/` · ADR-0009.

## Steps

1. Route in the module's `routes/web.php`. Admin pages go inside `Route::middleware('admin')` (= `auth` + `verified` + `can:admin.access`).
2. Policy method checking a permission enum; register it in the provider's `$policies`.
3. Read side: a Query object that eager-loads everything the view renders (`php artisan make:query <Module> <Name>`).
4. Write side: FormRequest with `toData()` → Action (`php artisan make:action <Module> <VerbNoun>`).
5. Thin controller: authorize, call one Action/Query, return a view or `to_route(...)->with(Flash::success(__('...')))`.
6. View in `app/Modules/<Module>/resources/views/` (referenced as `<module-kebab>::path`). Need a new widget? `php artisan make:ui-component <name>` and add it to `/_styleguide`.
7. Translation keys in **both** `lang/en` and `lang/fa`.
8. Add a navigation item in the provider (`NavigationArea::Admin` or `NavigationArea::App`) if the page should appear in a menu.
9. Check the page in fa (RTL) and en (LTR), light and dark mode, then:
   ```bash
   npm run format && npm run build
   composer check
   ```
