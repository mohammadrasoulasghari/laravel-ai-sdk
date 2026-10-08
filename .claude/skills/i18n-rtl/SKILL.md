---
name: i18n-rtl
description: Add or change user-facing text with Persian (default, RTL) and English (LTR) translations, Jalali dates, and verify RTL/LTR rendering. Use whenever you touch visible text.
---

# i18n & RTL

Reference: `app/Modules/Blog/lang/{en,fa}/*.php`, `lang/{en,fa}/ui.php`, `config/locales.php`, `app/Shared/Support/DateFormatter.php`.

## Where keys live
| Text | File | Usage |
|---|---|---|
| Module text | `app/Modules/<M>/lang/{en,fa}/<file>.php` | `__('blog::posts.created')` |
| Shared UI words (Save, Cancel…) | `lang/{en,fa}/ui.php` | `__('ui.save')` |
| Validation messages/attributes | `lang/{en,fa}/validation.php` (`attributes`) | automatic |
| HTTP error pages / problem titles | `lang/{en,fa}/errors.php`, `problems.php` | automatic |

## Rules
- Both locales get **every** key (arch test `TranslationsTest` compares them). Persian: natural, formal-friendly tone, use ZWNJ (‌) correctly (`می‌شود`, `نوشته‌ها`), Persian punctuation «» ، ؟.
- Placeholders identical in both: `:name`, `:count`.
- Never concatenate translated fragments; pass parameters.
- Plurals: `trans_choice()` with `{0} … |{1} … |[2,*] …` (Persian rarely needs plural forms; keep them anyway for en).
- Dates: `<x-ui.date :value="$date" />` (+ `with-time` / `relative`). Never format dates manually in views.
- Numbers in fa may stay Latin digits (Vazirmatn renders both); be consistent within a page.
- Layout direction comes from `<html dir>`; use logical utilities only. For mixed-direction content (slugs, emails, code) add `dir="auto"` or `dir="ltr"` on the element.

## Verify
1. `composer test` (translation parity + Blade text arch tests).
2. Visit pages in both locales (`POST /locale/fa`, `/locale/en` via the switcher) and dark mode.
