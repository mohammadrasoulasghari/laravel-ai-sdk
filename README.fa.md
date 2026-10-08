<div align="center" dir="rtl">

# استارتر کیت لاراول AI-First

**پایهٔ ماژولار لاراول ۱۳ که قوانینش با ابزار اجبار می‌شود؛ تا انسان‌ها و ایجنت‌های کدنویس هوش مصنوعی کد یکدست و قابل‌بازبینی بنویسند.**

[English](README.md) · [شروع سریع](#شروع-سریع) · [چرا این پروژه](#چرا-این-پروژه) · [مستندات](#مستندات)

<img src="docs/images/styleguide-light.png" alt="UI Kit — حالت روشن" width="49%"> <img src="docs/images/styleguide-dark.png" alt="UI Kit — حالت تاریک" width="49%">

</div>

<div dir="rtl">

> **اشتباه نگیرید:** پکیج `laravel/ai` «SDK رسمی هوش مصنوعی لاراول» است و برای *صدا زدن مدل‌های AI از داخل برنامه* به کار می‌رود.
> این پروژه یک **استارتر کیت برای ساختار کل برنامه** است تا **ایجنت‌های کدنویس** (Claude Code، Cursor، Copilot، Codex…) بتوانند بدون انحراف از معماری شما آن را توسعه دهند. این دو مکمل هم‌اند.

## چرا این پروژه

ایجنت‌های هوش مصنوعی سریع کد می‌نویسند، ولی هر بار بی‌سروصدا ساختار تازه‌ای اختراع می‌کنند. دستورالعمل‌های متنی جلویش را نمی‌گیرند.

این کیت راه دیگری می‌رود: **هر قانون یک مجری دارد.** اگر قانونی را نشود با تست، قانون PHPStan، فرمت‌کننده یا هوک چک کرد، جزو «قوانین طلایی» نمی‌شود.

| قانون | مجری |
|---|---|
| منطق کسب‌وکار در کنترلر، مدل و ویو نباشد | قانون سفارشی PHPStan + تست‌های معماری Pest |
| ماژول‌ها به داخل هم دست نزنند | تست‌های معماری (`ModuleBoundariesTest`) |
| `final`، `strict_types` و تایپ کامل | Pint، PHPStan سطح max، پوشش تایپ ۱۰۰٪ |
| متن سخت‌کدشده یا CSS غیر RTL-safe ممنوع | تست‌های معماری Blade + تست هم‌ترازی ترجمه‌ها |
| ویرایش `.env`، `vendor/` و migrationهای کامیت‌شده ممنوع | هوک `PreToolUse` در Claude Code |
| ایجنت تا وقتی معماری شکسته است نمی‌تواند بگوید «تمام شد» | هوک `Stop` (تست معماری + PHPStan) |

همه چیز پشت یک دستور است: **`composer check`** — همان که CI اجرا می‌کند.

## شروع سریع

```bash
git clone https://github.com/mohammadrasoulasghari/laravel-ai-sdk.git my-app
cd my-app
make setup          # Docker (Sail): env، وابستگی‌ها، MySQL + Redis + Mailpit، migrate + seed، بیلد
```

آدرس **http://localhost** را باز کنید و با **`admin@example.com` / `password`** وارد شوید (فقط seed محلی).

بدون Docker (با SQLite): `cp .env.example .env`، در `.env` مقدار `DB_CONNECTION=sqlite` و `SESSION_DRIVER=file`، `CACHE_STORE=file`، `QUEUE_CONNECTION=sync` را بگذارید، سپس `touch database/database.sqlite && composer setup && php artisan serve`.

تبدیل به پروژهٔ خودتان با یک دستور:

```bash
php artisan starter:init --name="فروشگاه من" --package=acme/shop --url=https://shop.test --remove-example --fresh-git
```

## چه چیزی داخلش هست

- 🧱 **مونولیت ماژولار** — هر دامنهٔ کسب‌وکار در `app/Modules/<Name>` (Action، DTO، Query، Policy، Event، مدل، ویو، ترجمه، route، migration).
- 🧭 **مسیر روشن نوشتن/خواندن** — `FormRequest ← کنترلر نازک ← Action (نوشتن) / Query (خواندن) ← ویوی Blade یا API Resource`؛ بدون Repository.
- 👤 **ماژول‌های آماده** — احراز هویت، کاربران، نقش‌ها و دسترسی‌ها، تنظیمات، پنل مدیریت و ماژول مرجع **Blog** (وب + API + رویداد + اعلان صف‌دار + تست).
- 🎨 **UI Kit و i18n** — حدود ۲۰ کامپوننت `<x-ui.*>`، Tailwind 4، حالت تاریک، **فارسی (RTL) و انگلیسی (LTR)**، تاریخ شمسی، صفحهٔ Style Guide و صفحات خطای سفارشی.
- 🔌 **API** — نسخه‌دار `/api/v1` با Sanctum، خطاهای RFC 9457، مستندات OpenAPI خودکار در `/docs/api`.
- 🔒 **امنیت پیش‌فرض** — CSP با nonce، هدرهای امنیتی، CORS، محدودیت نرخ، کوکی امن.
- 📈 **مشاهده‌پذیری** — لاگ JSON با correlation id، `/up` و `/health/ready`، Horizon، Pulse، Telescope (فقط local).
- ⚙️ **ژنراتورها** — `make:module` و بقیه، کدی می‌سازند که **بدون ویرایش** از `composer check` رد می‌شود.
- 🤖 **لایهٔ AI-First** — `CLAUDE.md`/`AGENTS.md`، ۹ ساب‌ایجنت، ۱۱ اسکیل، ۶ دستور، هوک‌های اجبار و Laravel Boost.

## کار با ایجنت‌ها

در Claude Code یک قابلیت را از صفر تا صد بسازید:

```text
/feature فرم «تماس با ما» که به پشتیبانی ایمیل بزند، با محدودیت نرخ و صندوق ورودی در پنل مدیریت
```

مسیر: معمار (پلن و ADR) ← سازندهٔ بک‌اند و UI ← نویسندهٔ تست ← بازبین کد ∥ ممیز امنیت ← تأییدکنندهٔ مستقل (`composer check`) ← مستندساز ← کامیت.

**واقعاً کار می‌کند؟** بله؛ یک ایجنت تازه که فقط `CLAUDE.md` را خوانده بود، با `/feature` یک ماژول «تماس با ما» (فرم عمومی، محدودیت نرخ، ایمیل صف‌دار، صندوق مدیریت، فارسی/انگلیسی، ۴۷ تست) ساخت و همهٔ چک‌ها سبز شد. ۱۳ نکتهٔ اصطکاک آن به اسکیل‌ها و ژنراتورها برگشت. همین کیت بعداً برای ساخت یک پلتفرم کامل با چند ایجنت موازی (git worktree) استفاده شد.

## دستورهای اصلی

| دستور | کار |
|---|---|
| `composer check` | همهٔ گیت‌های CI؛ تا سبز نشده «تمام» نیست |
| `composer fix` | Rector ← Pint ← Prettier |
| `make setup` / `up` / `down` / `check` | میان‌برهای Docker (Sail) |
| `php artisan make:module Contact` | اسکلت ماژول با ثبت provider |
| `php artisan make:action Contact SendMessage` | Action + DTO + تست |
| `php artisan permissions:sync` | ساخت نقش‌ها و دسترسی‌های همهٔ ماژول‌ها |

## مستندات

[فهرست مستندات](docs/README.md) · [معماری](docs/architecture.md) · [قراردادها](docs/conventions.md) · [workflowها](docs/workflows) · [افزونه‌ها](docs/add-ons) · [ADRها](docs/adr) · [گزارش ساخت (فارسی)](docs/FINAL_REPORT.md)

## مشارکت

مشارکت شما خوش‌آمد است؛ از [CONTRIBUTING.md](CONTRIBUTING.md) شروع کنید. قانون اصلی: `composer check` باید سبز باشد و تغییر در یک قرارداد به [ADR](docs/adr/template.md) نیاز دارد. لطفاً [آیین‌نامه رفتار](CODE_OF_CONDUCT.md) را رعایت کنید. برای آسیب‌پذیری‌ها [SECURITY.md](SECURITY.md) را ببینید.

## مجوز

[MIT](LICENSE). اگر این کیت وقتتان را گرفت، یک ⭐ به پیدا شدنش کمک می‌کند.

</div>
