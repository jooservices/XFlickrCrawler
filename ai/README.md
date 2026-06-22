# AI documentation map (XFlickr Crawler)

How AI tools use this package — not runtime application code.

## Read first

1. [AGENTS.md](../AGENTS.md) — non-negotiable rules
2. [`.github/skills/`](../.github/skills/) — canonical skill bodies
3. [docs/04-development/04-ai-skills.md](../docs/04-development/04-ai-skills.md) — contributor workflow

## Canonical skills (`.github/skills/`)

### Foundation (from dto, adapted)

- `repo-quality-foundation` — quality gates, Git flow, hooks
- `code-style-and-conventions` — Pint, PHPStan, PHPCS, PHPMD
- `architecture-and-design-principles` — SOLID, minimal diff
- `class-purpose-and-module-map` — XFlickr `src/` ownership
- `documentation-sync` — docs, README, AGENTS, skills alignment
- `review-and-risk-assessment` — P0/P1/P2 review
- `commit-and-pr-authoring` — Conventional Commits, PR hygiene
- `php-package-development` — Laravel package patterns
- `coverage-and-lint-guard` — lint and coverage policy
- `ci-hooks-maintenance` — CaptainHook and GitHub Actions
- `task-routing-and-intent-map` — route tasks to skills
- `change-type-taxonomy` — classify changes
- `dependency-and-versioning-policy` — Composer deps
- `security-hardening` — secrets, credentials
- `release-management` — release branches and tags

### Domain (XFlickr)

- `crawl-pipeline-integrity` — jobs, targets, pagination
- `runtime-config` — laravel-config keys and app profiles
- `database-migration-safety` — `xflickr_*` schema
- `queue-horizon-operations` — `xflickr` queue, dispatch command
- `testing-and-quality-gates` — PHPUnit, `composer check`

Legacy index under [`ai/skills/`](skills/) mirrors domain skill names; canonical bodies live in `.github/skills/`.

## By tool

| Tool | Entry |
|------|-------|
| **Cursor** | `AGENTS.md`, [`.cursor/rules/`](../.cursor/rules/), `.github/skills/` |
| **Claude** | `CLAUDE.md` → `AGENTS.md` |
| **GitHub Copilot** | [`.github/copilot-instructions.md`](../.github/copilot-instructions.md) |
| **VS Code** | copilot-instructions + `.github/skills/` |

## Human documentation

- Hub: [docs/README.md](../docs/README.md)
- Install: [docs/01-getting-started/install.md](../docs/01-getting-started/install.md)
- Gaps: [docs/05-maintenance/01-risks-legacy-and-gaps.md](../docs/05-maintenance/01-risks-legacy-and-gaps.md)
