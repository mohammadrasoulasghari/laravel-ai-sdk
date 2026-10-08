# Payments

## When to use

You charge money: one-off orders, invoices or subscriptions.

## Options

| Market | Gateway | Recommended package |
|---|---|---|
| Iran (IRR/Toman) | Zarinpal, IDPay, Pay.ir, Sadad, Mellat … | **`shetabit/payment`** (multi-gateway driver) or the gateway's own SDK |
| International | Stripe | **`laravel/cashier`** (Stripe) — subscriptions, invoices, webhooks |

Verify the current version, Laravel 13 compatibility and gateway API version with the `researcher` agent before installing. Record the choice in an ADR.

## Module to create

`php artisan make:module Billing` — owns `Payment` (and `Invoice`/`Subscription` if needed) models, a `PaymentStatus` enum, Actions (`StartPayment`, `VerifyPayment`, `RefundPayment`), callback/webhook controllers and admin screens.

## Integration points

- **Gateway contract** — `Billing\Contracts\PaymentGateway` (`purchase()`, `verify()`), bound in the provider to a Zarinpal or Stripe implementation selected by config (`config/billing.php`, keys in `.env.example`: e.g. `ZARINPAL_MERCHANT_ID=`, `STRIPE_SECRET=`). Actions depend only on the contract.
- **Amounts** — integers in the smallest unit (Rial for Iranian gateways, cents for Stripe) wrapped in a `Money` value object (`php artisan make:value-object Billing Money`). Never floats.
- **Flow (redirect gateways like Zarinpal)** — `StartPayment` creates a `pending` payment with an idempotency key → redirect to gateway → callback route calls `VerifyPayment`, which is idempotent (a payment already `paid` is not verified twice) and wraps the status change in a transaction.
- **Webhooks (Stripe)** — Cashier's webhook route; verify signatures; exclude the route from CSRF in `bootstrap/app.php`.
- **Events** — dispatch `PaymentSucceeded` / `PaymentFailed` so other modules (Orders, Subscriptions) react without importing Billing internals.
- **CSP** — `form-action 'self'` in `App\Shared\Http\Middleware\SecurityHeaders` blocks form posts to gateways; redirect with a `302` instead, or extend the policy for the gateway origin. Stripe Checkout is a redirect too.
- **Permissions-Policy** — `payment=()` is set by default; relax it only if you embed a payment request UI.

## Tests to add

- `StartPayment`: creates a pending payment, amount stored as integer.
- `VerifyPayment`: success, gateway failure, amount mismatch, already-verified (idempotent) — all with a fake `PaymentGateway`.
- Callback route: tampered/unknown authority → error, no state change.
- Webhook: invalid signature → 4xx.
- `Http::fake()` for any real SDK HTTP calls; never hit the gateway.
