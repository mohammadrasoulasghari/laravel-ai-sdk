---
description: Scaffold a new module with the project generator and register it
argument-hint: <ModuleName>
---

Create the module **$ARGUMENTS** using the `new-module` skill:

1. `php artisan make:module $ARGUMENTS` (registers the provider in `bootstrap/providers.php`).
2. Open the generated files and adapt names to the domain; follow `.claude/skills/new-module/SKILL.md`.
3. Run `composer check`. Report the created files.
