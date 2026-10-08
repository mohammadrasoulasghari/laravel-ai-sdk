# ADR-0011: Jalali dates via hekmatinasser/verta

- Status: Accepted
- Date: 2026-10-08

## Context
Persian locale must show Solar Hijri dates. Candidates: `morilog/jalali` (last release 2025-12), `hekmatinasser/verta` (v9, 2026-05, Carbon-like, Laravel 13 compatible).

## Decision
Use `hekmatinasser/verta` behind a single helper `App\Shared\Support\DateFormatter` and the `<x-ui.date>` component. Storage is always UTC Gregorian; conversion happens only at presentation.

## Consequences
Swapping the library touches one class.
