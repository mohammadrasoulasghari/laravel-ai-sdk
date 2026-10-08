# ADR-0005: No repository pattern; Actions + Query Objects over Eloquent

- Status: Accepted
- Date: 2026-10-08

## Context
Repositories over Eloquent usually re-expose Eloquent with worse ergonomics and leak anyway.

## Decision
- Writes: an `Action` class (single public `handle()` method) receives an immutable DTO and uses Eloquent directly.
- Complex reads: a `Query` object in `Queries/` returns a Builder, paginator or collection.
- Simple reads (`find`, route-model binding) may happen in controllers via route binding only; controllers never build queries (enforced by arch tests).

## Consequences
Less indirection. Swapping the persistence engine is not a goal; if it becomes one, a new ADR introduces repositories for that module only.

## Alternatives considered
- Repository + interface per model: rejected (YAGNI).
