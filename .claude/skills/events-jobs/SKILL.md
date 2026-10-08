---
name: events-jobs
description: Add domain events, (queued) listeners and jobs that are idempotent and retry-safe, wired through the module provider. Use for side effects, cross-module reactions and background work.
---

# Events, listeners, jobs

Reference: `app/Modules/Blog/Events/PostPublished.php`, `Listeners/NotifyAuthorOfPublication.php`, `Notifications/PostPublishedNotification.php`, `Providers/BlogServiceProvider.php` (`$listen`).

## Events
- Past-tense name, `final readonly`, carry **IDs and scalars/DTOs**, not models or requests (safe to serialize, safe across modules).
- Events are public API: other modules may listen. Dispatch from Actions via injected `Dispatcher`, **after** the transaction.

## Listeners
- Register in the provider: `protected array $listen = [PostPublished::class => [NotifyAuthorOfPublication::class]];`
- Slow or external work → `implements ShouldQueueAfterCommit` (or `ShouldQueue`), `$tries`, `$backoff`.
- **Idempotent**: re-load state by id and exit early if the work is no longer needed or already done.

## Jobs
- `Jobs/` classes implement `ShouldQueue` (arch test), use `Queueable`, set `$tries`, `backoff()`, `$timeout`; consider `ShouldBeUnique` / `WithoutOverlapping` middleware for exclusivity.
- Make the effect idempotent: upserts, "already processed" flags, unique keys — a retry must not double-charge/double-send.
- Queue names: `default`, `notifications`, `long` (configured in `config/horizon.php`).
- Scheduling: `routes/console.php` → `Schedule::job(new PruneStaleDrafts)->daily()->onOneServer();`

## Notifications
- Notify a user: `$user->notify(new XNotification(...))`. Notify an address that is not a user (support inbox): `Notification::route('mail', $email)->notify(...)` in a Listener (Listeners may use Facades and read another module's Contract, e.g. `SettingsStore`).
- `MailMessage::line()`/`subject()` render Markdown: **escape user-supplied text** (`e()` is not enough for Markdown — strip `[`, `]`, `(`, `)`, `*`, `_`, `` ` `` or use a Blade mail view with `{{ }}`) to prevent link injection. Set `->replyTo($visitorEmail)` instead of putting addresses in the body.

## Tests
- Event dispatched: `Event::fake([PostPublished::class]); … Event::assertDispatched(...)`.
- Wiring: `Event::fake(); Event::assertListening(PostPublished::class, NotifyAuthorOfPublication::class);`
- Listener/job behaviour: call `handle()` directly with `Notification::fake()`/`Queue::fake()` and assert idempotency by calling twice.

## Checklist
- [ ] Event payload is IDs/DTOs; dispatched after commit
- [ ] Queued work is idempotent and has retry policy
- [ ] Wiring + behaviour tested
