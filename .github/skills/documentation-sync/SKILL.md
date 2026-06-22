---
name: documentation-sync
description: "Use when: syncing docs with real repository behavior; updating development guides after script/config changes; correcting architecture or workflow drift; and keeping docs trustworthy for contributors."
---

# Documentation Sync Skill

## Quick Start

1. Identify source of truth (composer scripts, hooks, workflow files, code behavior).
2. Compare docs against actual implementation.
3. Update docs with minimal, accurate edits.
4. Verify examples and command names are executable.

## Repository Truth Sources

- `composer.json` scripts
- `captainhook.json` hook behavior
- `.github/workflows/*.yml` CI/release behavior
- `src/` implementation for runtime behavior docs

## Core Workflow

1. Pick a documentation area (setup, coding standards, CI, architecture, examples).
2. Collect real behavior from source of truth files.
3. Patch docs to remove drift and stale commands.
4. Ensure wording is precise and actionable.
5. Cross-link related docs when behavior changes.

## Audit checklist

- Title matches the real subject and audience.
- Purpose and scope are visible near the top.
- Commands and workflow names match `composer.json`, hooks, and workflows exactly.
- Runtime claims match `src/` behavior and tests.
- Business-facing summaries stay readable without hiding developer detail.
- Important decisions and limitations are easy to scan.
- Duplicated guidance points back to the owning file instead of drifting independently.
- AI-facing instructions clearly say what to do, what not to do, and where the source of truth lives.

## Drift Prevention Rules

- Never invent command names not present in `composer.json`.
- Keep branch names, trigger conditions, and gate order aligned with workflows.
- Use consistent terminology across docs sections.
- Use `JOOservices DTO Library` as the product name and `jooservices/dto` only for the Composer package identifier.
- Keep documented runtime gaps visible instead of smoothing them over.

## Failure Playbook

- Docs conflict across files:
  - Prioritize source-of-truth configs, then normalize all related docs.
- Docs too abstract:
  - Add concrete command examples and expected outcomes.
- Frequent drift in same area:
  - Add a maintenance checklist entry in that doc.
- Source of truth is ambiguous:
  - Keep the current verified fact, then label the unresolved part as `Needs clarification`, `Missing decision`, `Assumption`, or `Outdated / should be verified`.

## Definition Of Done

- Documentation matches current implementation.
- Commands/examples are valid.
- Related pages are updated consistently.
- Any unresolved ambiguity is explicitly marked instead of being guessed away.
