# Contributing

Contributions to `jooservices/xflickr-crawler` should keep **XFlickr Crawler** aligned with its crawl architecture, repository quality gates, and contributor guidance.

For development details, see [docs/04-development/07-contributing.md](./docs/04-development/07-contributing.md) and [AGENTS.md](./AGENTS.md).

## Git workflow summary

Aligned with `jooservices/dto`:

- `master` is the stable release branch and should receive only approved release PRs and urgent hotfix PRs
- `develop` is the integration branch for normal feature and fix work
- create `feature/*` and `fix/*` branches from the latest `develop`, then open the PR back into `develop`
- create `release/<version>` from the latest `develop` for release metadata and final stabilization, then open the PR into `master`
- after the release PR is merged into `master`, create the release tag and merge `master` back into `develop`
- use `hotfix/*` only for urgent production fixes from `master`, then merge back into both `master` and `develop`
- do not commit directly to `develop` or `master`

## Requirements

- PHP 8.5+
- Composer
- MySQL and Redis for full test coverage
- read [AGENTS.md](./AGENTS.md) before changing crawl behavior

## Setup

```bash
composer install
```

## Quality gate

```bash
composer check
```

This runs `lint:all` (Pint, PHPCS, PHPStan, PHPMD) and `composer test`.

Before commit or pull request, run the relevant checks for your change and ensure they pass.

## Architecture rules

- start crawls only via `FlickrService::connection()` — no OAuth in this package
- store app credentials in laravel-config (`xflickr_app.{profile}`), not `.env`
- one queued job per Flickr API page; rate-limit before every HTTP call
- bulk persistence via `upsertMany()` / `insertOrIgnore()`

## Pull requests

Pull requests should explain what changed, why, and how it was tested. Use [.github/pull_request_template.md](./.github/pull_request_template.md).

## Security

Do not report vulnerabilities in public issues. Follow [SECURITY.md](./SECURITY.md).

## AI contributors

AI and assisted contributors must read [AGENTS.md](./AGENTS.md) and [.github/skills/](./.github/skills/) before making changes. Run `composer check` before commit.
