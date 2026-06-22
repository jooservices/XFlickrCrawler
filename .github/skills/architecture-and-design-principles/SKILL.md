---
name: architecture-and-design-principles
description: "Use when: deciding how to change this package; choosing between minimal extension and refactor; reviewing public API impact; and keeping runtime, docs, tests, and contributor expectations aligned."
---

# Architecture and Design Principles Skill

## Purpose

This skill tells agents how to think when making changes in `jooservices/dto`.

## When to use

- Architecture or module-boundary decisions
- Laravel consumer-application guidance
- Design-pattern selection or review
- Event, transaction, or business-logic placement questions

## When NOT to use

- Pure command lookup with no architecture impact
- Formatting-only fixes that do not change structure

## Inputs

- User request
- Real repository files
- Existing docs, tests, and runtime behavior

## Outputs

- Minimal architecture-consistent change
- Explicit pattern rationale when a pattern is selected
- Updated docs/tests when public behavior or standards change

## Core principles

- SOLID
- KISS
- DRY
- YAGNI
- Explicit over clever
- Prefer minimal, local changes over broad rewrites
- Preserve public behavior unless the change explicitly updates the contract
- Treat docs and tests as part of the implementation
- Prefer additive evolution over breaking behavior
- Keep runtime truth ahead of documentation convenience
- Respect the package boundary: this is a library, not an application framework

## Package-specific principles

- `Core/` is the public foundation surface; change it carefully
- `Engine/`, `Hydration/`, `Normalization/`, and `Meta/` form a pipeline and should stay loosely coupled by role
- Attributes are declarative metadata, not a promise that runtime wiring already exists
- Validation timing and casting order are behavioral contracts
- Schema output should be described honestly, especially where it is intentionally shallow

## Agent guardrails

- Do not invent support for partially wired attributes
- Do not “fix” architectural boundaries by moving logic into new layers unless the existing structure is clearly failing
- Do not hide compatibility risks inside style or refactor commits
- Do not update docs to imply support that tests and runtime do not back up
- If requirements are unclear, conflicting, missing, or impossible based on real code, stop and ask the user
- Do not silently infer, assume, or decide when repository truth is missing or conflicting

## Laravel application architecture

Default application flow:

```text
Request -> Controller -> FormRequest -> Service -> Repository -> Model
```

- Controllers are thin and contain no business logic
- FormRequests handle validation and authorization
- Services own business logic, transactions, and event dispatch timing
- Repositories own query and persistence logic
- Models must not become fat business-logic containers
- Jobs and Commands orchestrate through Services
- Repositories do not dispatch events or manage transactions
- DTOs are the standard data contract and mapping layer
- Use enums, constants, or configuration instead of hard-coded domain values

## Events and transactions

- Events must be dispatched from Services
- Events must not be dispatched from Controllers, Jobs, Commands, Models, or Repositories unless an explicit documented exception already exists
- Prefer Event Subscribers for related event groups
- Use standalone Listener classes only when independent, reusable, or cross-context
- Multi-model writes must be wrapped in database transactions controlled by Services
- Domain and application events must be dispatched only after successful persistence
- If a transaction is used, events should be dispatched after successful commit
- Events must not replace direct method calls inside the same transaction

## Design patterns

- Use well-known, common patterns only
- Do not invent custom pseudo-patterns or fake architecture names
- Pick patterns based on actual code need, not trend or abstraction preference
- Every selected pattern must explain what problem it solves, why it fits, how current code implements it, benefit, and risk

Preferred patterns:

- Service Layer
- Repository
- DTO / Data Mapper
- Factory
- Strategy
- Adapter / Bridge
- Event-driven / Subscriber
- Command / Action
- Builder only when object construction is genuinely complex

Avoid:

- unnecessary interface per class
- manager/helper god classes
- traits as hidden architecture
- abstraction without current concrete need or clear extension point
- forced CQRS/Event Sourcing unless the repository actually needs it

## Explicit logic rule

- Business logic must not be hidden in Model boot methods, observers, or traits unless explicitly documented and justified
- Critical business logic must live in Services

## Change review questions

1. Is this a public API change or an internal implementation change?
2. Which module actually owns this behavior?
3. Is the change additive, behavioral, or breaking?
4. What tests prove the intended contract?
5. What docs need to move with the code?
6. What assumptions about current limitations must remain visible?

## Execution steps

1. Inspect the real repository before changing anything.
2. Name the owning module or application layer.
3. Apply the smallest local change consistent with that owner.
4. Justify any selected design pattern using problem, fit, implementation, benefit, and risk.
5. Update docs and tests when behavior or standards change.
6. Run the relevant repository checks.

## Stop conditions

- Requirements are unclear, conflicting, missing, or impossible based on real code.
- The requested architecture contradicts package boundaries or existing runtime behavior.
- A required file, command, tool, or workflow is missing and no repository-backed alternative exists.

## Definition of done

- The change follows repository architecture rather than fighting it
- Public behavior impact is intentional and tested
- Documentation and examples tell the same story as the runtime

## Related skills

- `class-purpose-and-module-map`
- `code-style-and-conventions`
- `coverage-and-lint-guard`
- `runtime-compatibility-guard`

## Related documents

- `docs/00-architecture/06-application-architecture-standards.md`
- `docs/00-architecture/04-modules-and-domains.md`
- `docs/04-development/04-code-quality.md`
