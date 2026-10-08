---
name: new-module
description: Create a new business module in app/Modules (provider, routes, lang, permissions, folders) and wire it into the app. Use when a feature needs its own domain/bounded context.
---

# New module

Reference: `app/Modules/Blog` (complete), `docs/architecture.md`, ADR-0004/0006.

## Steps
1. Decide the name: StudlyCase domain noun (`Contact`, `Billing`, `Course`). One module per bounded context; if it is just a new page of an existing domain, add it to that module instead.
2. Generate: `php artisan make:module Contact`
   - creates `Providers/ContactServiceProvider.php` (registered in `bootstrap/providers.php` under `// modules`),
   - `Enums/ContactPermission.php` (`contact.manage`, granted to `admin` by default),
   - `routes/web.php` (public + `admin` group), `routes/api.php` (`/api/v1/contact`), `lang/{en,fa}/contact.php`, empty folders.
3. Translate `lang/fa/contact.php` (the generator writes English placeholders into both). Delete `routes/api.php` if the module has no API, and delete folders you will not use (they only contain `.gitkeep`).
4. Data model → `migration-model` skill. Write paths → `new-action`. Screens → `web-crud`. API → `api-endpoint`.
5. Navigation (in `bootModule()` of the provider), like Blog:
   ```php
   $this->app->make(Navigation::class)->add(new NavigationItem(NavigationArea::Admin, 'contact::contact.nav', 'admin.contact.messages.index', ContactPermission::Manage->value, 'admin.contact.*', order: 50));
   ```
6. Seeder (optional): `Database/Seeders/ContactSeeder.php`, add to `database/seeders/DatabaseSeeder.php`.
7. Run `php artisan permissions:sync` locally after adding permissions.
8. `composer check`.

## Rules
- Other modules may use only your `Contracts/`, `Data/`, `Enums/`, `Events/`. Expose a Contract (interface in `Contracts/`, bound in `$bindings`) if another module needs to call you.
- Never import another module's `Models`, `Actions`, `Queries`, `Http` … (arch test `ModuleBoundariesTest`). Exception: `App\Modules\Users\Models\User`.
- Delete the `.gitkeep` of folders you fill; remove empty folders you will not use before committing.

## Checklist
- [ ] Provider registered in `bootstrap/providers.php`
- [ ] Permissions enum + registration; nav items with abilities
- [ ] `lang/en` and `lang/fa` have identical keys
- [ ] `composer check` green
