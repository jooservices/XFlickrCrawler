---
name: coverage-and-lint-guard
description: "Use when: adding or reviewing code that must satisfy style, static analysis, maintainability, and coverage requirements; triaging failures from Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer, or coverage checks."
---

# Coverage and Lint Guard Skill

## Repository truth

- `Pint` is the primary formatting authority
- `PHP-CS-Fixer` handles a narrow PHPDoc delta
- `PHPCS` checks structure
- `PHPStan` checks types and correctness
- `PHPMD` checks maintainability
- Long-term coverage standard is 95% statement coverage
- CI enforces the 95% minimum statement coverage threshold
- One reusable config file exists per quality tool; do not combine tool configs into one file

## Config map

- `pint.json`: Pint formatting
- `.php-cs-fixer.dist.php`: narrow PHPDoc cleanup
- `phpcs.xml`: PHPCS structural checks
- `phpstan.neon`: PHPStan strict static analysis
- `phpmd.xml`: PHPMD complexity and design smells
- `phpunit.xml`: PHPUnit tests and strict failure behavior
- `captainhook.json`: local hooks and Conventional Commit validation

## Conflict rules

- If Pint conflicts with PHPCS or PHP-CS-Fixer, Pint wins
- PHPCS must not duplicate Pint formatting rules
- PHP-CS-Fixer stays limited to narrow non-conflicting cleanup
- PHPStan runs at the strictest feasible level
- PHPMD thresholds must not reject valid DTO, model, or factory patterns
- No new PHPStan `ignoreErrors` entries without a narrow scope and clear reason
- No new PHPMD global exclusions unless proven necessary by real DTO package code
- If checker failures represent real code smell, fix the code instead of suppressing
- Any suppression must be reported in final WHAT/WHY/HOW notes
- PHPUnit must fail on warnings, notices, deprecations, risky tests, incomplete tests, and unexpected output

## Local command order

1. `composer lint:pint`
2. `composer lint:phpcs`
3. `composer lint:phpstan`
4. `composer lint:phpmd`
5. `composer lint:cs`
6. `composer test`
7. `composer test:coverage` when coverage-sensitive behavior changes
8. `composer check` for a combined lint-and-test gate
9. `composer ci` for a CI-style lint-and-coverage gate
10. `composer instructions:verify` when AI instruction adapters or policy files change

## Coverage policy

- Add unit tests for isolated logic
- Add integration tests when behavior crosses hydrator, normalizer, mapper, or engine boundaries
- Favor regression tests for public behavior, exceptions, and edge cases
- Do not stop at green tests if statement coverage is likely to fall below 95%
- Prefer real DTO fixtures, small local stubs, and PHPUnit test doubles; do not add a mocking dependency without a repository-backed need

## Failure playbook

- `lint:pint` fails:
  - Run `composer lint:pint:fix` or `composer lint:fix`
- `lint:phpcs` fails:
  - Fix structural violations before touching analysis suppressions
- `lint:phpstan` fails:
  - Fix types and signatures, avoid broad ignores or new `ignoreErrors`
- `lint:phpmd` fails:
  - Reduce complexity or split responsibilities
- `lint:cs` fails:
  - Keep PHPDoc cleanup narrow and non-overlapping with Pint
- Coverage fails:
  - Add or deepen tests until behavior and threshold are both covered

## Definition of done

- Style, structure, type analysis, and maintainability checks are green
- Tests cover the intended change and realistic edge cases
- Coverage impact has been considered explicitly

## Stop conditions

- A required command or config file is missing.
- Tool responsibilities conflict with real configuration.
- Checks emit errors, warnings, notices, deprecations, risky tests, or unexpected output that cannot be resolved within scope.

## Related documents

- `docs/04-development/04-code-quality.md`
- `docs/04-development/03-linting-standards.md`
- `docs/04-development/04-testing.md`
