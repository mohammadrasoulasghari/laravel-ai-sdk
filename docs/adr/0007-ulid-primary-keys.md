# ADR-0007: ULID primary keys

- Status: Accepted
- Date: 2026-10-08

## Context
Auto-increment IDs leak business volume and are enumerable in URLs/APIs. UUIDv4 hurts index locality.

## Decision
All domain tables use `ulid('id')->primary()` with Eloquent `HasUlids`. Foreign keys use `foreignUlid()`. Polymorphic relations use `ulidMorphs()` (Sanctum tokens, spatie/permission `model_id`, notifications). Framework tables (jobs, failed_jobs, cache, pivot tables of spatie) keep their defaults.

## Consequences
26-char string keys, lexicographically sortable by time. Third-party migrations that assume bigint user ids must be adjusted when published.

## Alternatives considered
- Auto-increment + public UUID column: two identifiers to keep straight.
- UUIDv7: equally fine; ULID chosen for shorter URL form and first-class Laravel support.
