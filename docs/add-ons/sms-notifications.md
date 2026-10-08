# SMS notifications

## When to use

OTP codes, order updates or reminders by SMS — common for Iranian users where SMS is the default channel.

## Recommended package

Provider SDKs: **Kavenegar** (`kavenegar/laravel`) or **Ghasedak** (`ghasedak/laravel` / `ghasedak/php`). Verify the current package names, versions and Laravel 13 support with the `researcher` agent. A thin HTTP client with Laravel's `Http` facade is often enough and easier to test.

## Module to create

`php artisan make:module Sms` — owns the `SmsSender` contract, the provider implementations, the notification channel and (optionally) a log of sent messages.

## Integration points

- **Contract** — `Sms\Contracts\SmsSender` (`send(string $mobile, string $message): void`, plus `sendTemplate()` for Kavenegar "verify lookup" / Ghasedak templates). Bind in the provider by `config('sms.driver')`: `kavenegar`, `ghasedak`, `log` (local default — writes to the log instead of sending).
- **Notification channel** — `Sms\Channels\SmsChannel` calls `SmsSender`; notifications implement `toSms($notifiable)`; register the channel with `Notification::extend('sms', …)` in the provider's `bootModule()`. Notifications then list `'sms'` in `via()`, exactly like `app/Modules/Blog/Notifications/PostPublishedNotification.php` uses `mail`.
- **Recipient** — the `User` model needs a mobile number (new migration in the Users module) and `routeNotificationForSms()`. Normalise numbers with a `MobileNumber` value object (`09xxxxxxxxx` / `+989xxxxxxxxx`).
- **Queue** — notifications implement `ShouldQueue` and use the `notifications` queue (`config/horizon.php`).
- **Config** — `config/sms.php`; `.env.example`: `SMS_DRIVER=log`, `KAVENEGAR_API_KEY=`, `GHASEDAK_API_KEY=`, `SMS_SENDER=`.
- **Rate limits** — throttle OTP sending per mobile + IP (see the `auth` limiter in [`docs/api.md`](../api.md)).

## Tests to add

- Channel: `Http::fake()` the provider endpoint, assert the request body; provider error → exception is reported, not swallowed.
- Notifications: `Notification::fake()` + `assertSentTo($user, …, fn ($n, $channels) => in_array('sms', $channels))`.
- `MobileNumber` value object: valid/invalid formats (unit test).
- OTP endpoint: throttled after N attempts (429).
