# AI Skills Usage Guide

## Start here

For any non-trivial task, read:

- `AGENTS.md`
- `CLAUDE.md`
- `ai/README.md`

Canonical skills live under `.github/skills/`. Update those first, then sync adapters.

## Recommended workflow

1. Read `AGENTS.md`.
2. Route with `.github/skills/task-routing-and-intent-map/SKILL.md`.
3. Classify with `.github/skills/change-type-taxonomy/SKILL.md`.
4. Load domain skills (`crawl-pipeline-integrity`, `runtime-config`, etc.).
5. Implement with tests.
6. Run `composer check` (or `composer ci` before PR).
7. Sync docs per `.github/skills/documentation-sync/SKILL.md`.

## Common recipes

| Task | Skills |
|------|--------|
| Crawl / jobs / fetchers | `crawl-pipeline-integrity`, `class-purpose-and-module-map` |
| Config / app profiles | `runtime-config` |
| Migrations | `database-migration-safety` |
| Queues / Horizon | `queue-horizon-operations` |
| Tests / CI | `testing-and-quality-gates`, `coverage-and-lint-guard` |
| Docs only | `documentation-sync` |
| Release | `release-management`, `commit-and-pr-authoring` |

## Maintenance

When repository behavior changes, update `.github/skills/` first, then `AGENTS.md`, `ai/README.md`, and `.github/copilot-instructions.md`.

Run `composer instructions:verify` to catch adapter drift.
