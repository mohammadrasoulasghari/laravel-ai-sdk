# ADR-0006: Module boundaries and public API

- Status: Accepted
- Date: 2026-10-08

## Context
Modules must not reach into each other's internals, otherwise they become one big ball of mud.

## Decision
A module's **public API** consists only of these namespaces:
`Contracts\` (interfaces bound in the module provider), `Events\`, `Data\`, `Enums\`.
Other modules may depend on these and nothing else.

Exception: `App\Modules\Users\Models\User` is the authenticatable identity used across the app (relations such as `Post::author()`, policies, `Auth::user()`). It is whitelisted explicitly in the arch test.

`App\Shared` may be used by every module; `App\Shared` must not depend on any module.

## Consequences
Enforced by `tests/Arch/ModuleBoundariesTest.php`. Communication beyond queries happens through events.

## Alternatives considered
- Separate identity module with a `UserContract`: Eloquent relations still need a concrete model; extra indirection for no gain.
