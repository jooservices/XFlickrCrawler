---
name: review-and-risk-assessment
description: "Use when: reviewing code, checking release readiness, assessing regression risk, identifying missing tests, evaluating public API impact, or answering 'is anything missing' from an agent or maintainer perspective."
---

# Review and Risk Assessment Skill

## Purpose

This skill switches an agent into reviewer mode instead of builder mode.

## Review priorities

- Behavioral regressions
- Public API drift
- Missing or weak tests
- Compatibility risk
- Documentation drift
- Workflow or release risk

## Repository-specific risks

- Validation happens before casting
- Property-level pipelines now run before validation, so review validation behavior against transformed input rather than raw input alone
- `Data::update()` and `Data::set()` rebuild through hydration and can now fail on invalid patches
- Some attributes and context members are still declared but not wired, especially `Context::$globalPipeline`, `Computed`, and `OptionalProperty`
- `DefaultFrom` and class-level `DiscriminatorMap` are now behavior-defining and should be reviewed like active hydration features
- Schema generation is still intentionally lightweight even though nested DTO and typed-array shapes are now expanded
- CI enforces the 95% minimum statement coverage standard
- Hooks and CI can drift if command names change

## Review checklist

1. What user-visible behavior changed?
2. Could this break hydration, normalization, validation, or schema output?
3. Are tests proving the exact changed contract?
4. Are docs/examples updated if external behavior changed?
5. Does the change conflict with current runtime limitations?
6. Does CI, release, or hook policy need adjustment?

## Response style

- Findings first
- Order by severity
- Include the file or behavior area
- Mention residual risk if no findings are present

## Definition of done

- The review focuses on bugs and risk rather than restating the diff
- Missing tests, compatibility concerns, and docs drift are called out explicitly
