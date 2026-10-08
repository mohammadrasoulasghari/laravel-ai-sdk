---
name: blade-component
description: Create or extend a UI kit Blade component (<x-ui.*>) with RTL-safe classes, design tokens, dark mode, a11y and a style guide example.
---

# UI kit component

Reference: `resources/views/components/ui/input.blade.php` (form control), `alert.blade.php` (variants), `modal.blade.php` (Alpine behaviour), `app/Shared/View/Components/Date.php` (class component). Tokens: `resources/css/tokens.css`.

## Steps
1. Check the kit first (`ls resources/views/components/ui`) — extend an existing component with a prop before adding a new one.
2. Generate: `php artisan make:ui-component tabs` → `resources/views/components/ui/tabs.blade.php`.
3. Write it:
   - `@props([...])` with defaults; document usage in a `{{-- --}}` header.
   - Merge attributes: `{{ $attributes->class('…') }}` so callers can add classes.
   - Colors **only** from tokens (`bg-surface`, `text-fg`, `text-fg-muted`, `border-border`, `bg-primary`, `text-primary-fg`, `bg-danger-soft` …). Need a new color? Add a variable to `tokens.css` (both `:root` and `.dark`) and map it in `app.css` `@theme inline`.
   - Direction: logical utilities only (`ms-/me-/ps-/pe-/start-/end-/text-start/text-end/border-s/border-e/rounded-s/rounded-e`).
   - Text: `__('ui.…')` keys in `lang/en/ui.php` and `lang/fa/ui.php`.
   - Interactivity: Alpine (`x-data`); reusable behaviour as `Alpine.data()` in `resources/js/app.js`.
   - a11y: roles/aria attributes, keyboard support (Escape closes overlays), `sr-only` labels for icons, focus returns to trigger.
   - Needs PHP logic (formatting, services)? Make a class component in `app/Shared/View/Components` and register it in `SharedServiceProvider` (`Blade::component(Class::class, 'ui.name')`).
4. Add an example of every variant/state to `resources/views/styleguide.blade.php` (visit `/_styleguide`, toggle dark mode and switch fa/en).
5. `npm run format && npm run build && composer test`.

## Checklist
- [ ] No physical direction classes, no raw palette colors, no hard-coded text
- [ ] Works in dark mode and RTL
- [ ] Style guide updated
