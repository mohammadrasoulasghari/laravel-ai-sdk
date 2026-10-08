# Add-ons

Optional capabilities that many projects need but the core kit deliberately does **not** ship.

> **Rule: add-ons are documented, not installed in the core.** The starter stays small, fast to
> audit and free of dependencies you might not use. Each page below is a recipe for adding the
> capability to *your* project, as its own module, following the same conventions as the core.

Before installing any package, ask the `researcher` agent to verify the current stable version and
its Laravel 13 / PHP 8.3 compatibility (CLAUDE.md "Never" list). Record the choice in an ADR
(`/adr <topic>`, skill `write-adr`).

| Add-on | Use it when | Page |
|---|---|---|
| Multi-tenancy | One deployment serves several isolated customers | [multi-tenancy.md](multi-tenancy.md) |
| Payments | You charge money (Zarinpal & other Iranian gateways, or Stripe via Cashier) | [payments.md](payments.md) |
| SMS notifications | OTP, alerts or reminders by SMS (Kavenegar, Ghasedak) | [sms-notifications.md](sms-notifications.md) |
| Two-factor authentication | Admin or user accounts need TOTP | [two-factor-auth.md](two-factor-auth.md) |
| Error tracking | Production errors must reach Sentry (or similar) | [error-tracking.md](error-tracking.md) |
| Media uploads | Users upload avatars, images or attachments | [media-uploads.md](media-uploads.md) |
| Search | Full-text search beyond `LIKE` queries | [search.md](search.md) |

## Common shape of every add-on

1. `php artisan make:module <Name>` — the add-on owns its module (or extends an existing one).
2. Wrap the vendor SDK behind an interface in `Contracts/`, bind it in the provider's `$bindings`; Actions depend on the interface, tests on a fake.
3. Secrets in `.env.example` (empty) → `config/*.php` → injected `Config`; never `env()` elsewhere.
4. Tests never hit the network (`Http::fake()`, fake implementations of the contract).
5. Docs: CHANGELOG `[Unreleased]`, ADR, and `docs/api.md` if endpoints were added.
