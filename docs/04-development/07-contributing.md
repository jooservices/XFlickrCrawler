# Contributing

Contributions to **XFlickr Crawler** should stay aligned with the repository's crawl architecture, quality gates, and Git workflow.

## Git workflow

Branch roles:

- `master`: stable release branch; production-ready only; no direct normal-development commits
- `develop`: integration branch for features and normal fixes; source branch for `release/<version>`
- `feature/*`: branch from latest `develop`; PR into `develop`
- `fix/*`: branch from latest `develop`; PR into `develop`
- `hotfix/*`: urgent production fix branch from `master`; PR into `master`; then merge back into `develop`
- `release/<version>`: release branch from latest `develop`; used for release metadata and final stabilization only; PR into `master`

For normal work such as features, fixes, refactors, tests, and active documentation updates:

```bash
git checkout develop
git pull origin develop
git checkout -b feature/<short-name>
# or
git checkout -b fix/<short-name>
```

Open the pull request back into `develop`.

Release preparation uses the approved release flow:

- create `release/<version>` from the latest `develop`
- update `CHANGELOG.md`, README, version references, and release metadata on `release/<version>`
- open the release PR into `master`
- merge the approved release PR into `master`
- create tag `vX.Y.Z` from `master`
- merge `master` back into `develop`

Do not update changelog, README, version references, or release metadata directly on `master`.

Hotfixes are the only exception to the normal feature and fix flow:

- create the hotfix branch from `master`
- open the pull request back into `master`
- after merge, synchronize the hotfix change back into `develop`

During development:

- keep the working branch updated with its parent branch
- prefer rebasing onto the parent branch to keep history clean
- make sure the branch is conflict-free and current before opening a pull request

Never commit directly to `develop` or `master` except for explicitly approved emergency procedures.

Recommended branch naming:

- `feature/<short-description>`
- `fix/<short-description>`
- `refactor/<short-description>`
- `hotfix/<short-description>`
- `release/<version>`
- `docs/<short-description>`
- `chore/<short-description>`

## Architecture constraints

Read [AGENTS.md](../../AGENTS.md) before changing `src/` or crawl behavior.

Key rules:

- Crawl entry only via `FlickrService::connection()` methods.
- No OAuth in this package.
- App credentials in laravel-config (`xflickr_app.{profile}`), not `.env`.
- One queued job per Flickr API page.
- `FlickrRequestLimiter::acquire()` before every HTTP job.
- Bulk persistence via `upsertMany()` / `insertOrIgnore()`.

## Before opening a pull request

Run:

```bash
composer lint:pint:fix
composer lint:all
composer test
composer check
```

Also confirm:

- no failing checks remain
- no debug code, temporary code, or unrelated file changes remain in scope
- the branch is synced with its parent branch
- behavior changes include tests
- docs are updated when public behavior or integration steps change

## Commit messages

CaptainHook enforces Conventional Commit messages:

```text
type(scope): Description
```

Valid types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`, `build`, `revert`.

## Pull request expectations

Use [.github/pull_request_template.md](../../.github/pull_request_template.md).

Before opening a PR:

- sync with the parent branch
- resolve all conflicts
- ensure `composer check` passes locally
- keep the PR scope focused
- include a clear summary, test evidence, and risk notes

CI must pass, but CI does not replace local verification before commit or PR.

## Documentation changes

When editing docs:

- use the canonical product name **XFlickr Crawler**
- use `jooservices/xflickr-crawler` only for the Composer package identifier
- do not document OAuth as a package feature
- do not document `.env` Flickr API keys as supported configuration

## Security

Do not report vulnerabilities in public issues. Follow [SECURITY.md](../../SECURITY.md).

## Related documents

- [Setup](./01-setup.md)
- [Testing](./02-testing.md)
- [CI/CD](./03-ci-cd.md)
- [AI skills](./04-ai-skills.md)
- [CONTRIBUTING.md](../../CONTRIBUTING.md)
