---
name: release-management
description: "Use when: preparing and executing package releases; validating tag-based release flow; creating GitHub releases; publishing updates to Packagist; and ensuring release quality gates are green."
---

# Release Management Skill

## Quick Start

1. Confirm `develop` and `master` are in the expected state for the approved release flow.
2. Create `release/<version>` from the latest `develop`.
3. Update release metadata on `release/<version>` only.
4. Run local quality gates (`composer lint:all`, `composer test`).
5. Merge the release PR into `master`, then create and push semantic version tag (`vX.Y.Z`).
6. Verify release workflow, Packagist publish steps, and merge `master` back into `develop`.

## Repository Truth

- Release flow is tag-driven (`v*.*.*`) in `.github/workflows/release.yml`.
- Validation job runs tests before creating release artifacts.
- Publish job triggers Packagist update for stable tags.
- Commit messages are enforced by CaptainHook and PR titles are enforced by the semantic PR workflow.
- Approved repository flow is:
  - feature or fix branch -> PR to `develop`
  - `develop` -> `release/<version>` -> release metadata updates -> PR to `master`
  - merge release PR into `master` -> tag `vX.Y.Z`
  - merge `master` back into `develop`

## Preconditions

- You have push permission for tags.
- CI status for the release branch and release PR is green.
- Version has been agreed and documented.
- `release/<version>` has been created from the latest `develop`.

## Core Workflow

1. Prepare:
  - Checkout and update `develop`.
  - Create `release/<version>` from the latest `develop`.
  - Update changelog, README, version references, and release metadata on `release/<version>` only.
   - Ensure no pending uncommitted changes.
2. Validate locally:
   - `composer lint:all`
   - `composer test`
   - Confirm docs and examples are in sync with the release content
3. Open the release PR:
  - source: `release/<version>`
  - target: `master`
  - keep scope limited to release stabilization and release metadata
4. Merge the approved release PR into `master`.
5. Tag and push from `master`:
   - `git tag vX.Y.Z`
   - `git push origin vX.Y.Z`
6. Monitor release workflow completion.
7. Verify GitHub release notes and Packagist update.
8. Merge `master` back into `develop` after the release tag and release artifacts are complete.

## Failure Playbook

- Release validation test fails:
  - Fix issue on `release/<version>`, rerun checks, and update the release PR.
- Release passes locally but fails in CI:
  - Compare local checks with `validate` job behavior and workflow permissions.
- Tag format invalid:
  - Use strict `vX.Y.Z` naming.
- Packagist publish fails:
  - Check repository secrets and rerun release job.
- Release drift found on `master`:
  - Move release metadata corrections back onto the active `release/<version>` branch or a follow-up release branch; do not patch release metadata directly on `master`.

## Definition Of Done

- Release PR from `release/<version>` into `master` was used.
- Release tag exists and matches intended version.
- Release workflow completed successfully.
- GitHub release is available and correct.
- Packagist reflects the new version.
- `master` has been merged back into `develop`.
