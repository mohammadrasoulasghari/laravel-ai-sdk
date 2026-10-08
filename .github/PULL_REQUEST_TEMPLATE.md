## Summary

<!-- What does this change and why? Link the issue / plan (docs/plans/…) if any. -->

## Type

- [ ] feat — new feature
- [ ] fix — bug fix
- [ ] refactor / perf
- [ ] docs
- [ ] test
- [ ] build / ci / chore
- [ ] Breaking change (`!` in the commit title, migration notes below)

## Checklist (definition of done — see CLAUDE.md)

- [ ] `composer check` is green (and `npm run build` if the front-end changed)
- [ ] Tests for every new Action (success + each exception) and route (guest / 403 / invalid / success)
- [ ] Translations in fa **and** en; pages checked in RTL and LTR (light + dark)
- [ ] Docs updated: `CHANGELOG.md` `[Unreleased]`, `docs/api.md` for endpoints, ADR for decisions
- [ ] No new `@phpstan-ignore`, baseline entries or skipped tests
- [ ] No module-boundary violations; new packages checked by the `researcher` agent
- [ ] PR title is a Conventional Commit: `feat(blog): add post scheduling`

## Screenshots (UI changes)

| fa (RTL) | en (LTR) |
|---|---|
| <!-- screenshot --> | <!-- screenshot --> |

## Notes for reviewers

<!-- Risky areas, migrations, follow-ups. -->
