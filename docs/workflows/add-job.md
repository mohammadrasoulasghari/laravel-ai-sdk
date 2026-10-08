# Add an event, listener or job

Side effects, cross-module reactions and background work go through events and queued
listeners/jobs on Redis + Horizon. Everything queued must be idempotent and retry-safe.

Full recipe: [`.claude/skills/events-jobs/SKILL.md`](../../.claude/skills/events-jobs/SKILL.md) · Reference: `app/Modules/Blog/Events/PostPublished.php`, `Listeners/NotifyAuthorOfPublication.php`.

## Steps

1. **Event** in `Events/`: past-tense name, `final readonly`, carries IDs/scalars/DTOs. Dispatch it from the Action through an injected `Dispatcher`, after the transaction.
2. **Listener** in `Listeners/`, registered in the provider's `$listen` array. Slow or external work implements `ShouldQueueAfterCommit` (or `ShouldQueue`) with `$tries` and `$backoff`.
3. **Job** in `Jobs/`: implements `ShouldQueue` (arch test), sets `$tries`, `backoff()`, `$timeout`; reloads state by id and exits early if the work is already done.
4. Pick a queue: `default`, `notifications` or `long` (`config/horizon.php`).
5. Recurring work: add it to `routes/console.php`, e.g. `Schedule::job(new PruneStaleDrafts)->daily()->onOneServer();` (the Sail `scheduler` container runs `schedule:work`).
6. Tests: `Event::fake()` + `Event::assertListening(...)` for wiring; call `handle()` twice to prove idempotency.
7. Run and watch:
   ```bash
   composer check
   ./vendor/bin/sail up -d     # horizon + scheduler containers
   ```
   Dashboard: `http://localhost/horizon`.
