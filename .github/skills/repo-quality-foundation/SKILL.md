---
name: repo-quality-foundation
description: "Use when: starting any non-trivial task in this repository; aligning an AI agent with coding standards, coverage, hooks, CI, release policy, documentation rules, and current runtime limitations."
---

# Repository Quality Foundation Skill

This is the baseline skill for `jooservices/xflickr-crawler`.

## Repository truth

- Runtime target: PHP 8.5+, Laravel 12
- Package: `jooservices/xflickr-crawler`
- Product name: **XFlickr Crawler**
- Architecture: queued Flickr crawl pipeline with MySQL catalog, Redis rate limiting, and API auditing

## Quality gates

- Format with `Pint`
- Run `PHPCS`, `PHPStan`, and `PHPMD` for structural, type, and maintainability checks
- Run `PHPUnit` tests (Redis required for rate-limit and job feature tests)
- Long-term coverage target: **95%** statement coverage (dto standard)
- CI minimum threshold: see `docs/04-development/03-ci-cd.md`
- Keep PHPCS structural only; Pint wins formatter conflicts
- Fix real checker failures in code instead of suppressing them

## Command map

- `composer lint:fast`
- `composer lint`
- `composer lint:all`
- `composer lint:fix`
- `composer test`
- `composer test:coverage`
- `composer instructions:verify`
- `composer check`
- `composer ci`

## Architecture baseline

- Package internals follow the existing module map under `src/`
- Laravel application guidance follows `Request -> Controller -> FormRequest -> Service -> Repository -> Model`
- Controllers stay thin; Services own business logic; Repositories own query and persistence logic
- DTOs are the standard data contract and mapping layer
- Services own transaction boundaries and dispatch events only after successful persistence or commit
- Repositories must not dispatch events or manage transactions unless an explicit documented exception already exists
- Design patterns must be well-known, justified by current need, and documented with problem, fit, implementation, benefit, and risk

## Source-of-truth handling

When repository files disagree, prefer this order:

1. `composer.json` for command names and package/runtime metadata
2. `captainhook.json` for local hook behavior
3. `.github/workflows/*.yml` for CI and release behavior
4. `src/` plus tests for runtime behavior
5. supporting docs and prompt adapters

## Hooks and PR policy

- Conventional Commits are enforced by CaptainHook on `commit-msg`
- `pre-commit` runs PHP linting, `gitleaks protect --staged`, `composer lint:pint`, `composer lint:phpcs`, `composer lint:phpstan`, `composer lint:phpmd`, and `composer lint:cs`
- `pre-push` runs `gitleaks detect` and `composer test`
- PR titles must use the configured Conventional Commit types and start with an uppercase subject
- Keep `SECURITY.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `.github/ISSUE_TEMPLATE/`, and `.github/pull_request_template.md` aligned with current repository workflow when they exist.
- Vulnerability reports must not be handled in public issues; direct them to `SECURITY.md`.
- Issues and pull requests should use repository templates when those templates exist.

## Git branch strategy

- Branch roles:
  - `master`: stable release branch; production-ready only; accepts approved release PRs and urgent hotfix PRs; no direct commits; tag from this branch after release merge.
  - `develop`: integration branch; accepts feature and normal fix PRs; source branch for `release/<version>`; receives merge-back from `master` after release.
  - `feature/*`: branch from latest `develop`; PR into `develop`.
  - `fix/*`: branch from latest `develop`; PR into `develop`.
  - `hotfix/*`: urgent production fix branch from `master`; merge into `master`, then merge back into `develop`.
  - `release/<version>`: release-preparation branch from latest `develop`; only release metadata and stabilization changes; PR into `master`.
- Normal work starts from the latest `develop`.
- Only urgent hotfix work starts from `master`.
- Before creating a normal working branch:
  - checkout `develop`
  - pull latest remote changes
  - ensure local `develop` is current
- Normal feature or fix branching pattern:

```bash
git checkout develop
git pull origin develop
git checkout -b feature/<short-name>
# or
git checkout -b fix/<short-name>
```

- Normal working branches open PRs back into `develop`.
- Release branches are created from the latest `develop`, then open PRs into `master`.
- Release metadata such as changelog, README, version references, and release notes must be updated on `release/<version>`, not directly on `master`.
- After a release or hotfix lands in `master`, synchronize `master` back into `develop`.
- Keep the working branch synced with its parent branch during implementation.
- Prefer regular rebases against the parent branch to reduce conflicts and keep history clean.
- Before opening a PR, the branch must be conflict-free and current with its parent branch.
- Never commit directly to `develop` or `master` except for explicitly approved emergency procedures.
- Recommended branch prefixes:
  - `feature/`
  - `fix/`
  - `refactor/`
  - `hotfix/`
  - `release/`
  - `docs/`
  - `chore/`
- If protected branches are enabled, `develop` and `master` should be protected.
- If a rebase requires updating a remote personal branch, use `--force-with-lease` only when team policy allows force-push on personal branches.

## Commit and PR cleanliness

- Commits should be small, logical, and meaningfully named.
- Do not commit or open a PR with failing tests, lint, static analysis, warnings, or notices in the required checks.
- Do not leave debug code, temporary code, commented dead code, unrelated file changes, or formatting noise outside the intended scope.
- CI must pass, but CI does not replace the local responsibility to run and verify relevant checks before commit or PR.

## Runtime guardrails

Do not claim full runtime support for:

- `Computed`
- `Deprecated`
- `OptionalProperty`
- `Context::$globalPipeline`

Also remember:

- `DefaultFrom` is active for hydration fallback values from env, config, and supported static methods
- Class-level `DiscriminatorMap` is active for discriminator-based polymorphic hydration
- Property-level `#[Pipeline]` and `#[StrictType]` are behavior-defining in hydration
- Typed DTO arrays can be inferred from common PHPDoc forms such as `Type[]`, `array<Type>`, and `list<Type>`
- `CastWith` and `TransformWith` options payloads are not fully consumed
- `Data::update()` and `Data::set()` rebuild through hydration, so casting and validation can run again
- Nested schema generation is lightweight and expands common nested DTO and typed-array shapes, but it does not emit a full reference graph

## Always-follow workflow

1. Identify the touched module and public behavior impact.
2. Implement the smallest repository-consistent change.
3. Add or update tests.
4. Update docs when public behavior or workflow truth changes.
5. Run the relevant quality gates.
6. Finalize with Conventional Commit and PR-safe wording.

## Do not do this

- Do not invent support for declared-but-unwired runtime features.
- Do not treat prompt adapters as more authoritative than canonical skills or repository configs.
- Do not silently reconcile conflicting sources; if requirements are unclear, conflicting, missing, or impossible based on real code, stop and ask the user.
- Do not lower coverage, lint, hook, or CI gates to make a change pass.
- Do not suggest direct commits to `develop` or `master` for normal implementation work.
- Do not suggest skipping the parent-branch sync step before branching, before PR, or after long-running work.
- Do not suggest opening a PR with failing or noisy quality gates.
