# AGENTS.md — XFlickr Crawler

Read this file before touching code in `jooservices/xflickr-crawler`.

## Project identity

Standalone Laravel 12 package for queued Flickr crawling with MySQL persistence, per-connection API rate limiting, and API call auditing. OAuth stays in the host application. Flickr app credentials live in `jooservices/laravel-config` (`xflickr_app.{profile}`).

Repository standards follow [jooservices/dto](https://github.com/jooservices/dto) for documentation, governance, agent instructions, and Git flow.

## Non-negotiables

1. **Crawl entry** — start work only via `FlickrService::connection($key, $token, ?$appProfile)` methods (`contacts`, `photos`, `photosets`, `galleries`).
2. **No OAuth in package** — authentication stays in the host application.
3. **App credentials in laravel-config** — register `xflickr_app.{profile}` JSON (`apiKey`, `apiSecret`); never use `.env` `FLICKR_API_KEY` / `FLICKR_API_SECRET` in this package.
4. **Queued API calls** — one job per Flickr API page; never loop pages synchronously in services.
5. **Rate limiting** — every HTTP job must call `FlickrRequestLimiter::acquire($connectionKey)` before the API request.
6. **Bulk persistence** — use repository `upsertMany()` / pivot `insertOrIgnore()`; avoid per-row writes in fetchers.
7. **Database safety** — never run `migrate:fresh` or destructive SQL against non-test databases without explicit approval.
8. **Inspect source first** — do not invent features, commands, or supported behavior.
9. **Stop and ask** when requirements are unclear, conflicting, missing, or impossible based on real code.

## Must-read before work

- [Documentation hub](docs/README.md)
- [Architecture overview](docs/00-architecture/overview.md)
- [Data flow](docs/00-architecture/02-data-flow.md)
- [Module map](docs/00-architecture/03-module-map.md)
- [App profiles](docs/01-getting-started/app-profiles.md)
- [AI documentation map](ai/README.md)

## Must-read skills

Canonical skills live under [`.github/skills/`](.github/skills/). Start with:

- `.github/skills/repo-quality-foundation/SKILL.md`
- `.github/skills/crawl-pipeline-integrity/SKILL.md` (jobs, targets, pagination)
- `.github/skills/runtime-config/SKILL.md` (laravel-config keys)
- `.github/skills/database-migration-safety/SKILL.md` (schema changes)
- `.github/skills/queue-horizon-operations/SKILL.md` (Horizon, `xflickr:dispatch`)
- `.github/skills/testing-and-quality-gates/SKILL.md` (PHPUnit, lint)
- `.github/skills/class-purpose-and-module-map/SKILL.md` (ownership)
- `.github/skills/documentation-sync/SKILL.md` (docs + skills alignment)
- `.github/skills/review-and-risk-assessment/SKILL.md` (PR risk)

## Architecture

- `FlickrCrawlerManager` / `FlickrConnection` — public API
- `CrawlingService` — creates crawl runs and initial targets
- `FlickrSpiderService` — enqueue/dispatch targets
- `Fetch*PageJob` + `*Fetcher` — API call + persist + follow-up pagination
- `FlickrCatalogService` — bulk upsert catalog data

## DTO standard

DTOs extend `JOOservices\Dto\Core\Dto` with constructor property promotion. See `.github/skills/dto-contract-standards/SKILL.md` when present, or `jooservices/dto` docs.

## Quality gate

```bash
composer lint:fast    # Pint + PHPCS + PHPStan
composer lint:all     # + PHPMD
composer test
composer check        # lint:all + test
composer ci           # lint:all + test:coverage (CI)
composer instructions:verify
```

Pint is the formatting authority. PHPCS is structural only.

## Coverage

- Long-term target: **95%** statement coverage (dto standard).
- CI enforces the minimum documented in `docs/04-development/03-ci-cd.md`.

## Git branch workflow (dto)

- `master` — stable releases; tag `vX.Y.Z` here after release merge.
- `develop` — integration branch for features and fixes.
- `feature/*`, `fix/*` — branch from latest `develop`; PR into `develop`.
- `release/<version>` — from `develop`; PR into `master`; then merge `master` back into `develop`.
- `hotfix/*` — from `master`; merge to `master` and back to `develop`.
- Never commit directly to `develop` or `master`.

## Documentation sync

When behavior, commands, config keys, or quality gates change, update the nearest canonical doc and any skill or adapter that points to it. See `.github/skills/documentation-sync/SKILL.md`.

## Commit policy

- Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
- Do not commit while required gates are failing.
- Never commit as an automation identity; no AI `Co-authored-by` trailers.
