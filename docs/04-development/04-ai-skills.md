# AI Skills

This repository includes an AI skill pack for agents working in `jooservices/xflickr-crawler`.

## Purpose

The AI skill pack helps agents and contributors:

- follow crawl pipeline non-negotiables (queued pages, rate limiting, no OAuth)
- understand class and module ownership before editing
- route tasks to the right workflow
- respect lint, test, and `composer check` quality gates
- keep documentation aligned with real runtime behavior

## Entry points

Start with:

- [`AGENTS.md`](../../AGENTS.md) — non-negotiable rules and architecture summary
- [`ai/README.md`](../../ai/README.md) — local skill index for crawl-specific topics
- [`.github/skills/`](../../.github/skills/) — canonical skill source on GitHub

There is no root `CLAUDE.md` in this repository yet. Use `AGENTS.md` and `.github/skills/` as the primary policy surfaces.

## Canonical skill source

The source of truth lives in:

```
.github/skills/
```

Update canonical skills first when repository behavior, workflows, or constraints change.

Notable package-specific skills:

| Skill | When to use |
|-------|-------------|
| `crawl-pipeline-integrity` | Jobs, targets, dispatch, pagination follow-ups |
| `class-purpose-and-module-map` | Finding the right module owner in `src/` |
| `architecture-and-design-principles` | Package design constraints |
| `coverage-and-lint-guard` | `composer check`, coverage policy |
| `commit-and-pr-authoring` | Conventional commits and PR templates |
| `security-hardening` | Credential handling, secret incidents |
| `documentation-sync` | Keeping docs aligned with code |

See also the local index at [`ai/README.md`](../../ai/README.md) for crawl-operator skills (`runtime-config`, `queue-horizon-operations`, `database-migration-safety`).

## Recommended workflow

1. Read `AGENTS.md`.
2. Route the task with `task-routing-and-intent-map`.
3. Load task-specific skills from `.github/skills/`.
4. Inspect real code in `src/` before editing — do not assume behavior from memory.
5. Implement or review the change.
6. Run `composer check` before commit.
7. Update docs when public behavior or integration steps change.

## Practical rules for agents

Agents should:

- start crawls only via `FlickrService::connection()` — never bypass with direct job dispatch from host code examples
- never add OAuth flows to this package
- store app credentials in laravel-config (`xflickr_app.{profile}`), never `.env` Flickr keys
- enqueue one job per API page — never loop pages synchronously in services
- call `FlickrRequestLimiter::acquire()` before every HTTP job
- use repository bulk writes (`upsertMany`, `insertOrIgnore`) in fetchers
- never run `migrate:fresh` against non-test databases without explicit approval
- follow the approved Git flow (`develop` for normal work, `release/<version>` → `master` for releases)
- route vulnerability reports through `SECURITY.md`, not public issues

Agents should not:

- document OAuth as a package feature
- suggest `.env` Flickr API keys in package code or docs
- fake test results or skip `composer check`
- commit directly to `develop` or `master`
- add synchronous pagination loops to services

## Maintenance rule

When repository behavior changes:

1. Update `.github/skills/` and `AGENTS.md`.
2. Sync `docs/` and `ai/README.md` in the same change when user-facing behavior moves.
3. Keep `.github/copilot-instructions.md` aligned with `AGENTS.md`.

## Related documents

- [Contributing](./07-contributing.md)
- [Module map](../00-architecture/03-module-map.md)
- [CI/CD](./03-ci-cd.md)
