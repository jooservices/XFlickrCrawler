# CI/CD

## Workflows

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `ci.yml` | push/PR to `master`, `develop` | Security, lint matrix, tests + coverage |
| `release.yml` | tag `v*.*.*` | Validate, GitHub release, Packagist publish |

## `ci.yml` jobs

### Security

- `composer validate --strict`
- `composer audit --locked --abandoned=ignore`

### Lint matrix

Runs in parallel after security passes:

- Pint (`composer lint:pint`)
- PHPCS (`composer lint:phpcs`)
- PHPStan (`composer lint:phpstan`)
- PHPMD (`composer lint:phpmd`)
- AI instructions (`composer instructions:verify`)

### Tests & coverage

- Service: **Redis 7** (rate limiter + job feature tests)
- PHP **8.5** with **pcov**
- Command: `composer ci` (`lint:all` + `test:coverage`)
- Minimum statement coverage: **75%** (long-term target **95%**, dto standard)

Local parity:

```bash
composer check      # lint:all + test (no coverage file)
composer ci           # lint:all + test:coverage
```

## Git workflow (dto)

| Branch | Role |
|--------|------|
| `master` | Stable releases; tag `vX.Y.Z` here |
| `develop` | Integration for features and fixes |
| `feature/*`, `fix/*` | From `develop` → PR into `develop` |
| `release/<version>` | From `develop` → PR into `master` |
| `hotfix/*` | From `master` → PR into `master`, sync to `develop` |

### Release 1.0.0 flow

```bash
git checkout develop
git pull origin develop
git checkout -b release/1.0.0
# verify CHANGELOG.md and composer.json version
composer ci
git push -u origin release/1.0.0
# open PR release/1.0.0 → master
# after merge on master:
git checkout master && git pull
git tag -a v1.0.0 -m "Release 1.0.0"
git push origin v1.0.0
git checkout develop && git merge master && git push origin develop
```

## Commit and PR hygiene

CaptainHook enforces Conventional Commits when installed (`composer install` runs `captainhook install`).

PR titles should match: `type(scope): Subject`

## Secrets (release)

- `PACKAGIST_USERNAME` / `PACKAGIST_TOKEN` — optional; triggers Packagist update on stable tags

## Related

- [Testing](./02-testing.md)
- [Contributing](./07-contributing.md)
