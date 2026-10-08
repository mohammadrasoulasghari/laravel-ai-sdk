# Add a module

A module is one business domain (bounded context) in `app/Modules/<Name>`. If the feature is just a
new page of an existing domain, add it to that module instead.

Full recipe for agents: [`.claude/skills/new-module/SKILL.md`](../../.claude/skills/new-module/SKILL.md) · Reference: `app/Modules/Blog` · ADR-0004, ADR-0006.

With Claude Code: `/new-module Contact` (or `/feature <description>` for the whole pipeline).

## Steps

1. Generate the skeleton (provider registered in `bootstrap/providers.php`, permission enum, web/api routes, `lang/{en,fa}`, empty folders):
   ```bash
   php artisan make:module Contact
   ```
2. Translate `app/Modules/Contact/lang/fa/contact.php` (the generator writes English placeholders into both locales).
3. Add the data model:
   ```bash
   php artisan make:migration create_contact_messages_table --path=app/Modules/Contact/Database/Migrations
   ```
   then the model + factory → [migration-model skill](../../.claude/skills/migration-model/SKILL.md).
4. Add use cases: `php artisan make:action Contact SendMessage` (see [add-endpoint](add-endpoint.md), [add-blade-page](add-blade-page.md)).
5. Register navigation items and permissions in the provider's `bootModule()`, then persist permissions:
   ```bash
   php artisan permissions:sync
   ```
6. Verify:
   ```bash
   composer check
   ```

## Boundaries

Other modules may only use your `Contracts/`, `Data/`, `Enums/` and `Events/`. Need another module's
behaviour? Depend on its `Contracts\` interface or listen to its events. `tests/Arch/ModuleBoundariesTest.php` enforces this.
