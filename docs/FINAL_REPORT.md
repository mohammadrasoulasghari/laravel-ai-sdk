# گزارش نهایی — استارتر کیت لاراول AI-First (نسخهٔ ۱.۰.۰)

تاریخ: ۱۶ مهر ۱۴۰۵ (2026-10-08)

## ۱. خلاصه

یک استارتر کیت لاراولی در سطح production ساخته شد که برای شروع هر پروژهٔ جدید و توسعه با ایجنت‌های هوش مصنوعی (Claude Code، Cursor، Copilot و …) طراحی شده است. قوانین فقط نوشته نشده‌اند؛ هر قانون با تست معماری، قانون PHPStan، Pint/Rector یا هوک Claude Code **اجبار** می‌شود.

| شاخص | مقدار |
|---|---|
| لاراول / PHP | 13.35 / ≥ 8.3 |
| تعداد تست‌ها | ۸۲۳ تست Pest (واحد، فیچر، معماری) — همه سبز |
| تحلیل استاتیک | PHPStan/Larastan سطح **max** بدون baseline + قانون سفارشی «Eloquent در کنترلر ممنوع» |
| پوشش تایپ | ۱۰۰٪ |
| استایل | Pint (preset سخت‌گیرانه)، Rector، Prettier (Blade + Tailwind) |
| ADRها | ۱۸ تصمیم معماری مستند |

## ۲. چه ساخته شد

### زیرساخت و ابزار کیفیت
- محیط توسعه با یک دستور: `make setup` (Sail: app، MySQL 8.4، Redis، Mailpit، Horizon، scheduler).
- نقطهٔ ورود واحد کیفیت: `composer check` = lint + analyse + test + type coverage؛ `composer fix` برای اصلاح خودکار.
- گیت‌هوک‌ها با CaptainHook: Pint و PHPStan روی فایل‌های stage شده + اجبار Conventional Commits.
- CI در GitHub Actions: چک کامل روی PHP 8.3 و 8.4 با MySQL، پوشش ≥ ۸۰٪، `composer audit` و `npm audit`، و mutation testing روی PRها.

### معماری
- معماری ماژولار در `app/Modules` با پایهٔ `ModuleServiceProvider` (route، view، ترجمه، migration، policy، listener، ناوبری، دسترسی‌ها به‌صورت خودکار).
- الگوی **FormRequest → کنترلر نازک → Action / Query → View یا Resource**، DTOهای immutable، Value Objectها، Exceptionهای دامنه.
- مرز ماژول‌ها: فقط `Contracts`، `Data`، `Enums`، `Events` عمومی‌اند (با تست معماری اجبار می‌شود).
- کلید اصلی ULID، حالت strict مدل‌ها (جلوگیری از N+1 و خطاهای بی‌صدا).
- API نسخه‌دار `/api/v1` با Sanctum، خطاهای استاندارد RFC 9457 (problem+json)، مستندات OpenAPI خودکار با Scramble در `/docs/api`.

### ماژول‌ها
- **Auth**: ورود، ثبت‌نام، بازیابی رمز، تأیید ایمیل، توکن API، محدودیت نرخ.
- **Users**: مدیریت کاربران در پنل، `/api/v1/me`.
- **Roles**: نقش‌ها و دسترسی‌ها (spatie)، رجیستری دسترسی ماژول‌ها و دستور `permissions:sync`.
- **Settings**: تنظیمات قابل ویرایش با کش.
- **Admin**: پنل مدیریت ساده با ناوبری پویا.
- **Blog (ماژول مرجع)**: نوشته و دسته‌بندی با وب، API، رویداد، listener صف‌دار، اعلان ایمیلی، Value Object نامک (پشتیبانی فارسی)، feature flag با Pennant و تست کامل.

### فرانت‌اند
- Blade + Alpine.js + Tailwind CSS 4 با توکن‌های طراحی در یک فایل، حالت تاریک، فونت وزیرمتن.
- پشتیبانی کامل RTL (فارسی، پیش‌فرض) و LTR (انگلیسی) با کلاس‌های منطقی؛ تاریخ شمسی برای فارسی.
- UI Kit با ۲۰ کامپوننت `<x-ui.*>`، layoutهای app/guest/admin/error، صفحهٔ Style Guide (`/_styleguide`)، صفحات خطای سفارشی.

### امنیت و مشاهده‌پذیری
- هدرهای امنیتی و CSP مبتنی بر nonce، CORS محدود، کوکی امن در production، trusted proxies.
- لاگ JSON با correlation id در تمام درخواست‌ها و jobها، endpointهای `/up` و `/health/ready`.
- Horizon، Pulse، Telescope (فقط local)، دسترسی ابزارها فقط با مجوز `system.tools.view`.

### لایهٔ AI-First
- `CLAUDE.md` (حدود ۱۰۰ خط پرسیگنال) و `AGENTS.md` که به آن ارجاع می‌دهد.
- ۹ ساب‌ایجنت (researcher، architect، laravel-builder، blade-ui-builder، test-writer، code-reviewer، security-auditor، qa-verifier، docs-keeper).
- ۱۱ اسکیل گام‌به‌گام با مثال واقعی از ماژول مرجع.
- دستورات `/feature`، `/new-module`، `/review`، `/check`، `/adr`، `/audit`.
- هوک‌ها: اجرای Pint بعد از هر ویرایش، مسدودسازی ویرایش `.env`/vendor/migrationهای منتشرشده، اجرای تست معماری + PHPStan پیش از پایان کار ایجنت.
- Laravel Boost (MCP) با راهنمای جداگانه در `docs/ai/laravel-boost.md`.

### ابزارهای قابل استفادهٔ مجدد
- ژنراتورها: `make:module`، `make:action` (با `--no-data`)، `make:dto`، `make:query`، `make:value-object`، `make:ui-component` — کد تولیدشده بدون ویرایش از `composer check` عبور می‌کند.
- `php artisan starter:init` برای ساخت پروژهٔ جدید در یک دستور (نام، پکیج، URL، کلید، حذف ماژول نمونه، ریست تاریخچهٔ git)؛ حالت غیرتعاملی و dry-run.

## ۳. آزمون ایجنت (فاز ۱۲)

یک ایجنت تازه که فقط `CLAUDE.md` را خوانده بود، با جریان `/feature` یک ماژول «تماس با ما» کامل ساخت (فرم عمومی، محدودیت نرخ، اعلان ایمیلی صف‌دار، پنل مدیریت، فارسی/انگلیسی، ۴۷ تست) و همهٔ چک‌ها سبز شد. ۱۳ نقطهٔ اصطکاک گزارش کرد که همگی رفع شد (بهبود ژنراتورها، اسکیل‌ها، قراردادها و تنظیمات محدودیت نرخ). ماژول آزمایشی سپس حذف شد.

## ۴. ممیزی نهایی (فاز ۱۳)

بازبین کد و ممیز امنیت به‌صورت موازی کل کدبیس را بررسی کردند. یافته‌های مهم و وضعیت:

| شدت | یافته | وضعیت |
|---|---|---|
| بالا | ادمین می‌توانست خود را super-admin کند یا حساب super-admin را تصاحب کند | ✅ رفع + تست |
| متوسط | توکن‌های API منقضی نمی‌شدند | ✅ پیش‌فرض ۳۰ روز + حذف روزانهٔ توکن‌های منقضی |
| متوسط | seed شدن ادمین با رمز پیش‌فرض در production | ✅ نیاز به `ADMIN_PASSWORD` (حداقل ۱۲ کاراکتر) |
| متوسط | نبود trusted proxies (محدودیت نرخ و HSTS پشت load balancer) | ✅ `TRUSTED_PROXIES` |
| متوسط | حذف `Retry-After` از پاسخ‌های problem+json | ✅ رفع + تست |
| متوسط | race condition در ساخت نامک | ✅ تلاش مجدد محدود |
| متوسط | حذف کاربر، نوشته‌هایش را پاک می‌کرد | ✅ نویسنده null و «عضو سابق» |
| کم | تزریق Markdown در ایمیل، اعتبارسنجی ورودی ژنراتورها و starter:init، سخت‌سازی هوک‌ها، ناوبری موبایل، و … | ✅ رفع |

## ۵. تصمیم‌ها و فرض‌ها

- **Pest 4 به‌جای Pest 5**: Pest 5 به PHP 8.4 نیاز دارد و کف پروژه PHP 8.3 است (ADR-0003). با بالا بردن کف، ارتقا یک دستور است.
- **ماژول‌ها در `app/Modules`** بدون پکیج جداگانه (ADR-0004)؛ **بدون Repository** (ADR-0005).
- **احراز هویت دست‌ساز** روی primitives خود لاراول به‌جای Fortify/Breeze برای یکدستی با قراردادها (ADR-0008).
- **Alpine استاندارد** با `'unsafe-eval'` در CSP؛ نسخهٔ CSP آلپاین بیان‌ها را برای ایجنت‌ها بیش از حد محدود می‌کرد (ADR-0015).
- **تست‌ها به‌صورت محلی روی SQLite in-memory** و در CI روی MySQL (ADR-0012).
- **تحلیل استاتیک** روی کد خودمان در سطح max؛ فایل‌های config و migrationهای منتشرشدهٔ پکیج‌ها مستثنا هستند (ADR-0013).
- ماشین توسعه فاقد Redis و افزونهٔ phpredis بود؛ اجرا با Sail/Docker در نظر گرفته شده و تست‌ها به Redis وابسته نیستند.

## ۶. محدودیت‌ها

- اجرای واقعی CI در GitHub انجام نشده (ریپوی راه دور وجود نداشت)؛ همان دستورات به‌صورت محلی سبز هستند.
- پوشش خط کد (`composer test:coverage`) و mutation testing به pcov/xdebug نیاز دارند که روی این ماشین نصب نبود؛ در CI پیکربندی شده‌اند.
- تست مرورگر (Pest Browser/Playwright) اضافه نشده است.
- محتوای ماژول Blog نمونه است؛ با `starter:init --remove-example` حذف می‌شود.

## ۷. پیشنهادهای بعدی

1. ارتقای کف PHP به 8.4 و مهاجرت به Pest 5.
2. افزودن تست‌های مرورگر برای مسیرهای حیاتی (ورود، پنل مدیریت) و تست بصری RTL.
3. افزونه‌های مستند در `docs/add-ons/` (چندمستاجری، پرداخت زرین‌پال/Stripe، پیامک، احراز دومرحله‌ای، Sentry، جستجو) در صورت نیاز.
4. انتشار ریپوی عمومی، تنظیم GitHub branch protection با الزام CI سبز، و افزودن Dependabot/Renovate.
