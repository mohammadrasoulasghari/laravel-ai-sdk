# ADR-0009: Blade + Alpine + Tailwind 4 front-end with an internal UI kit

- Status: Accepted
- Date: 2026-10-08

## Context
Server-rendered Blade keeps one language and one deployment. Agents produce more consistent UI when constrained to a small component set.

## Decision
- Tailwind CSS 4 via `@tailwindcss/vite`; design tokens are CSS custom properties in `resources/css/tokens.css`, mapped into Tailwind with `@theme`. Dark mode uses the `dark` class on `<html>` (toggled by Alpine, persisted in `localStorage`, defaulting to system preference).
- RTL/LTR: `<html dir>` derived from the locale (`config/locales.php`); components use logical utilities only (`ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*`, `text-start`). Physical `ml-/mr-/pl-/pr-/left-/right-/text-left/text-right` are forbidden (arch test scans views).
- UI kit lives in `resources/views/components/ui/*` (`<x-ui.button>` ...). Module views use only these components.
- Fonts: Vazirmatn (self-hosted via npm) for both scripts.

## Consequences
No Livewire/Inertia; interactive needs use Alpine. Heavier interactivity would require a new ADR.
