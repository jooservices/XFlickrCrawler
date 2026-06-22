---
name: php-package-development
description: "Use when: developing or maintaining this jooservices/dto PHP package; adding attributes, casters, validators, transformers, schema generators; fixing hydration or normalization bugs; and shipping package changes with tests, docs, hooks, and CI in mind."
---

# PHP Package Development Skill

This skill standardizes how contributors work in this repository.

## Quick Start

1. Pick the change type (attribute, caster, validator, transformer, bugfix).
2. Implement minimal change in the correct `src/` module.
3. Add tests in mirrored `tests/Unit/` paths.
4. Add integration test when behavior crosses Engine/Hydrator/Normalizer boundaries.
5. Run `composer lint` and `composer test`.
6. If style fails, run `composer lint:fix`, then rerun lint and tests.
7. Finalize with a Conventional Commit message.

## Scope

Use this workflow for:
- Feature development inside `src/`
- Bug fixes with regression tests
- Extension points: attributes, casters, validators, transformers, schema
- PR hardening before merge

Do not use this skill for:
- Non-PHP infrastructure work unrelated to package behavior
- Broad refactors without tests and behavior constraints
- Release management outside repository CI/release workflows

## Prerequisites

- PHP 8.5+ and Composer 2 installed
- Dependencies installed with `composer install`
- Git hooks installed through CaptainHook (`post-install-cmd` / `post-update-cmd`)
- `gitleaks` installed locally to pass pre-commit and pre-push secret scans

## Repository Truth

- Runtime requirement: PHP >= 8.5 (`composer.json`)
- Core commands (`composer.json`):
  - `composer lint`
  - `composer lint:all`
  - `composer lint:fix`
  - `composer test`
  - `composer test:coverage`
  - `composer check`
  - `composer ci`
- Git hooks (`captainhook.json`): commit message regex, pre-commit linting, gitleaks scan, pre-push tests.
- CI gates (`.github/workflows/ci.yml`): security, lint matrix, tests, and the 95% statement coverage threshold.
- Long-term coverage standard: 95% statement coverage.
- Runtime gaps and limits (`docs/05-maintenance/01-risks-legacy-and-gaps.md`) must be treated as current behavior.

When sources disagree, prefer:

1. `composer.json` for commands and package metadata
2. `captainhook.json` for local hook behavior
3. `.github/workflows/*.yml` for CI and release behavior
4. `src/` plus tests for runtime behavior
5. supporting docs and prompt adapters

## Git workflow

- `master` is the stable release branch and must not receive direct normal-development commits.
- `develop` is the integration branch for normal package work.
- For normal package work, branch from the latest `develop`.
- Hotfixes are the only branch type that starts from `master`.
- Release preparation uses `release/<version>` branches created from the latest `develop`.
- Before branching for normal work:
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

- Keep the working branch updated with its parent branch throughout implementation.
- Prefer rebasing onto the parent branch instead of routine merge commits.
- Open PRs back to the same parent branch the work started from:
   - normal work -> `develop`
   - hotfix work -> `master`
   - release work -> `master`
- Release metadata changes belong on `release/<version>`, not directly on `master`.
- After a hotfix or release lands in `master`, synchronize the same change back into `develop`.
- Never commit directly to `develop` or `master` for normal work.
- Keep branch names short and task-oriented, for example `feature/...`, `fix/...`, `refactor/...`, `hotfix/...`, `release/<version>`, `docs/...`, or `chore/...`.
- If rebasing requires updating a remote personal branch, prefer `--force-with-lease` only when team policy allows it.

## Module Map

- `src/Attributes/`: declarative behavior via PHP attributes
- `src/Casting/`: type conversion and caster registry
- `src/Validation/`: validation rules and registry
- `src/Hydration/`: mapping and constructor argument resolution
- `src/Normalization/`: output shaping and transformer flow
- `src/Meta/`: reflection metadata and caches
- `src/Schema/`: JSON Schema and OpenAPI generation

## Test Decision Matrix

- Add unit tests only when:
   - Logic is fully isolated to one class/module
   - No orchestration change in Engine/Hydrator/Normalizer
- Add integration tests when any is true:
   - Input mapping + validation + casting behavior changed end-to-end
   - Normalization output contracts changed through transformer/serialization flow
   - Context flags (validation, permissive mode, serialization options, wrapping) influence behavior

## Always-Follow Workflow

1. Identify module and extension point under `src/`.
2. Create or confirm the correct working branch from the correct parent branch.
3. Implement smallest possible change with strict typing and module boundaries.
4. Rebase regularly against the parent branch during implementation.
5. Add tests:
   - Unit test in mirrored path under `tests/Unit/`
   - Integration test in `tests/Integration/` if flow crosses Engine/Hydrator/Normalizer boundaries.
6. Run quality gates locally:
   - `composer lint`
   - `composer test`
7. If style issues exist, run `composer lint:fix`, then rerun lint + tests.
8. If touching security-sensitive code or introducing new code paths, run secret and dependency checks as applicable.
9. Check whether docs, examples, or release notes should change.
10. Before PR, ensure the branch is synced with its parent branch, conflict-free, and clean on all required checks.
11. Use Conventional Commits in commit message.

If requirements are ambiguous:

- verify the repository truth first
- stop and ask the user when requirements are unclear, conflicting, missing, or impossible based on real code
- do not silently infer, assume, decide, or widen scope to “solve” the ambiguity

## Command Map

- Fast local gate: `composer lint:fast`
- Full local gate: `composer lint`
- Auto-fix style: `composer lint:fix`
- Test suite: `composer test`
- Coverage run: `composer test:coverage`
- Combined lint-and-test gate: `composer check`
- CI-equivalent script: `composer ci`

## Failure Playbook

- `composer lint:pint` fails:
   - Run `composer lint:pint:fix` or `composer lint:fix`
   - Re-run `composer lint:pint`
- `composer lint:phpcs` fails:
   - Fix structural issues or run the approved fixer path where appropriate
   - Re-run `composer lint:phpcs`
- `composer lint:phpstan` fails:
   - Fix type/signature issues first, avoid broad suppressions
   - Re-run `composer lint:phpstan`
- Coverage threshold fails:
   - Add or strengthen tests until coverage is back at or above 95%
- Pre-commit fails on `gitleaks`:
   - Remove or rotate detected secret and replace with env/placeholder
   - Re-stage and commit again
- Pre-push fails on tests:
   - Reproduce with `composer test`
   - Add/fix tests and push after green

## Security-At-Inception Rule

When generating new first-party code, perform security scanning and fix discovered issues before finalizing changes.

Minimum checks:
- `composer audit`
- Local secret scan via gitleaks hooks or manual gitleaks commands

## Do not do this

- Do not add new abstraction layers when the current module can own the change.
- Do not document or implement partially wired attributes as if they were already supported unless you also wire runtime code, tests, and docs together.
- Do not change public behavior under a `refactor` label or without regression coverage.
- Do not patch prompt adapters first when the canonical skill or repository docs are stale.
- Do not leave placeholders or TODO-style claims that imply work is complete.
- Do not branch normal work from `master`.
- Do not open a normal work PR into `master`.
- Do not open a PR while required checks still have errors, warnings, notices, or unresolved failures.

## Prompt Examples

- "Add a new validator for IBAN format and include unit + integration tests"
- "Implement a transformer for DateTimeImmutable RFC3339 output and update normalizer tests"
- "Fix hydration bug for nullable enum casting with regression test"
- "Add a property attribute that maps from nested key path and wire metadata support"

## Definition Of Done

A change is done when all are true:
- Correct module placement under `src/`
- Tests added/updated and passing
- Lint + static analysis passing
- Coverage impact reviewed for behavior changes
- No new secret/dependency security findings
- Docs updated when behavior/API changes

## PR Readiness Checklist

- Scope is minimal and module boundaries respected
- Unit/integration tests are sufficient for changed behavior
- `composer lint` and `composer test` are green locally
- Hooks pass (commit-msg, pre-commit, pre-push)
- Commit messages follow Conventional Commits
- Behavior changes are documented in docs when applicable
