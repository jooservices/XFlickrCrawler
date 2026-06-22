# Copilot Instructions For `jooservices/xflickr-crawler`

Read [AGENTS.md](../AGENTS.md) as the primary repository policy.

When generating or editing code:

- start crawls only via `FlickrService::connection()` — never add OAuth to this package
- store Flickr app credentials in laravel-config (`xflickr_app.{profile}`), never `.env` `FLICKR_API_KEY` / `FLICKR_API_SECRET`
- enqueue one queued job per Flickr API page — never loop pages synchronously in services
- call `FlickrRequestLimiter::acquire($connectionKey)` before every HTTP job
- use repository bulk writes (`upsertMany`, `insertOrIgnore`) in fetchers
- prefer the existing crawl pipeline architecture over new abstractions
- match repository-native style and naming, not just formatter output
- understand which class or module owns the behavior before editing — see [docs/00-architecture/03-module-map.md](../docs/00-architecture/03-module-map.md)
- stop and ask the user if requirements are unclear, conflicting, missing, or impossible based on real code
- keep tests and docs in the same change when public behavior moves
- respect runtime limitations from [docs/05-maintenance/01-risks-legacy-and-gaps.md](../docs/05-maintenance/01-risks-legacy-and-gaps.md)
- assume CI will enforce `composer check` (Pint, PHPCS, PHPStan, PHPMD, PHPUnit)
- never run `migrate:fresh` or destructive SQL against non-test databases without explicit approval
- follow the approved Git flow: normal work from `develop`, release preparation on `release/<version>` from `develop`, release PRs into `master`, then merge `master` back into `develop`
- do not route vulnerability handling through public issues; follow [SECURITY.md](../SECURITY.md)

Use skills in [.github/skills/](./skills/) for focused tasks:

- `crawl-pipeline-integrity` — jobs, targets, dispatch, pagination
- `class-purpose-and-module-map` — module ownership in `src/`
- `coverage-and-lint-guard` — `composer check` and coverage policy
- `commit-and-pr-authoring` — Conventional Commits and PR templates
- `security-hardening` — credential and secret handling

See also [docs/04-development/04-ai-skills.md](../docs/04-development/04-ai-skills.md).
