---
name: commit-and-pr-authoring
description: "Use when: writing commit messages, PR titles, PR summaries, or release-facing change descriptions; aligning wording with Conventional Commits, semantic PR rules, and repository labels."
---

# Commit and PR Authoring Skill

## Purpose

This skill helps agents describe changes in repository-native git and PR language.

## Commit message rules

- Use Conventional Commits
- Keep commits small and logically coherent
- Avoid vague messages such as `fix`, `update`, `change`, `test`, or `temp`
- Allowed types:
  - `feat`
  - `fix`
  - `docs`
  - `style`
  - `refactor`
  - `perf`
  - `test`
  - `chore`
  - `ci`
  - `build`
  - `revert`
- Shape:
  - `type(scope): Description`

## PR title rules

- Must use the same Conventional Commit type set
- Subject must start with an uppercase letter
- Scope is optional

## Branch and PR targeting rules

- Normal work branches from `develop` and opens PRs back into `develop`.
- Hotfix branches from `master` and opens PRs back into `master`.
- Release preparation branches from `develop` as `release/<version>` and opens PRs into `master`.
- Release metadata changes such as changelog, README, version references, and release notes must be prepared on `release/<version>`, not directly on `master`.
- After a release or hotfix PR is merged into `master`, `master` must be merged back into `develop`.
- Before opening a PR:
  - sync with the parent branch
  - resolve all conflicts
  - ensure required quality gates are clean
  - keep scope focused and avoid mixing unrelated work
- PR summaries should clearly state:
  - what changed
  - why it changed
  - affected areas
  - test and lint evidence
  - any relevant risk or migration note

## PR summary expectations

- State the user-visible or maintainer-visible outcome
- Call out tests run or expected
- Mention docs updates if behavior changed
- Mention compatibility or risk if relevant

## Label awareness

Changed files may trigger labels such as:

- `source`
- `tests`
- `documentation`
- `dependencies`
- `ci/cd`
- `configuration`

## Definition of done

- Commit and PR wording matches repository automation
- The summary explains the real scope without hiding risk
