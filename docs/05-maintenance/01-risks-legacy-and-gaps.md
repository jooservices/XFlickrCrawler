# Risks, legacy, and gaps

This file documents repository-backed gaps that should not be hidden by the documentation.

## Coverage gap

| Metric | Target | Current (approx.) |
|--------|--------|-------------------|
| Line coverage | **95%** (dto target) | **~80%** |
| CI minimum | **75%** | enforced in `ci.yml` / `release.yml` |

The maintainer standard is 95% minimum line coverage (aligned with `jooservices/dto`). CI enforces a **75%** floor via `composer ci` until the suite consistently meets 95%.

Under-tested areas as of the initial release:

- `FlickrPermitAcquirer` — retry loop with `sleep()` (covered indirectly; dedicated unit tests planned)
- Remaining `Fetch*PageJob` classes — share contacts job pattern; only contacts job has full feature coverage
- `XFlickrConfig` — runtime override branches via laravel-config
- `FlickrApiOutcomeClassifier` — auth and transient failure branches
- Gallery and photoset child-task chains — fewer feature tests than contacts/photos
- `Console/DispatchCrawlTargetsCommand` — scheduler command integration
- Some repository and model paths with low method coverage

**Policy:** Add focused tests when touching these areas. Do not lower the 95% target to match current numbers. Add a CI coverage threshold check once the repository consistently meets the target.

## OAuth boundary

This package intentionally does not implement OAuth. The host application must:

- run the Flickr OAuth 1.0a flow
- store user tokens securely
- pass token JSON to `FlickrService::connection()`

Documenting or implementing OAuth inside this package would violate the architecture boundary.

## App credential storage

Flickr app `apiKey` / `apiSecret` must live in `jooservices/laravel-config` as `xflickr_app.{profile}` JSON.

Risks if misconfigured:

- `FlickrAppNotConfiguredException` at job time when profile is missing
- Flickr error 96 (invalid signature) when user tokens were issued by a different app than the profile credentials
- Secret exposure if `apiSecret` or `token_payload` are logged

## Queue and scheduler dependency

Crawls do not progress without:

- a queue worker processing the `xflickr` queue
- `xflickr:dispatch` scheduled every minute

Starting a crawl only creates targets; dispatch is required to move them to jobs. Host apps that omit the scheduler will see targets stuck in `Pending` unless `CrawlingService` immediate dispatch covers the first batch.

## Redis single point of failure

Rate limiting and (typically) queue backing depend on Redis. Redis unavailability causes:

- denied rate-limit permits (jobs retry)
- dispatch failures if the queue uses Redis

There is no in-process fallback limiter.

## Database migration safety

Package migrations create `xflickr_*` tables. Host applications share the same database.

**Never** run `migrate:fresh` or destructive SQL against non-test databases without explicit approval.

## No synchronous crawl completion API

There is no public method to block until a crawl finishes. Host apps must poll `xflickr_crawl_runs` / `xflickr_crawl_targets` or build their own completion notifications.

## Duplicate data across connections

The same Flickr photo may be stored multiple times if crawled under different `connection_key` values. The package does not deduplicate across connections.

## Documentation policy for these gaps

The docs should:

- describe these gaps explicitly
- avoid presenting unsupported features (OAuth, sync crawls, `.env` app keys) as supported
- point contributors to tests and `AGENTS.md` when closing gaps

## Related documents

- [Testing](../04-development/02-testing.md)
- [CI/CD](../04-development/03-ci-cd.md)
- [AGENTS.md](../../AGENTS.md)
