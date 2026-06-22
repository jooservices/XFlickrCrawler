---
name: runtime-compatibility-guard
description: "Use when: changing runtime behavior, public APIs, hydration, validation, normalization, mutation semantics, or schema output; reviewing backward compatibility; or guarding against unsupported-feature drift."
---

# Runtime Compatibility Guard Skill

## Focus

Use this skill whenever a change can affect public behavior or developer expectations.

## Guardrails

- Preserve existing public behavior unless the change intentionally revises it
- Prefer additive changes over silent behavioral rewrites
- Add regression tests for bug fixes
- Update docs/examples when behavior changes

## Current limitations that must stay explicit

- Declared runtime gaps still include features such as `Context::$globalPipeline` and other placeholder attributes such as `Computed`, `Deprecated`, and `OptionalProperty`
- `DefaultFrom` and class-level `DiscriminatorMap` are now wired into hydration behavior and should be documented as active features
- Property-level `#[Pipeline]` and `#[StrictType]` are already wired into runtime behavior and should not be described as placeholders
- Typed DTO arrays can be inferred from common PHPDoc forms, but unsupported annotations should still be documented carefully
- `Data::update()` and `Data::set()` rebuild through hydration, so mutation semantics can now fail fast on invalid patches
- Schema generation expands common nested DTO and typed-array shapes, but still stops short of a full reference-based graph

## Review checklist

1. Does the change alter hydration or casting behavior?
2. Does the change affect validation timing or error messages?
3. Does the change affect normalization or serialization output?
4. Does the change alter mutability or update semantics?
5. Does the change require docs, examples, or release notes?
6. Does the change need integration coverage rather than unit coverage only?

## Definition of done

- Public behavior impact is understood and tested
- Unsupported features are not accidentally marketed as supported
- Compatibility notes are reflected in docs when needed
