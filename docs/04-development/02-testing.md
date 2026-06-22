# Testing

## Test framework

The repository uses PHPUnit with Orchestra Testbench, configured in `phpunit.xml`.

## Suite layout

| Directory | Purpose |
|-----------|---------|
| `tests/Unit` | Isolated class tests: services, fetchers, helpers, manager |
| `tests/Feature` | End-to-end crawl flows: job execution, error paths, crawl types |

Both suites are registered in `phpunit.xml`:

- `unit` — `tests/Unit`
- `feature` — `tests/Feature`

## Main commands

```bash
composer test
composer test:coverage
composer check
```

`composer test:coverage` runs PHPUnit with Xdebug coverage and writes `coverage.xml`.

`composer check` runs the full lint suite plus tests — use this before opening a pull request.

## What tests cover

Unit tests focus on:

- `FlickrCrawlerManager` and `FlickrConnection` entry API
- `FlickrSpiderService` target dispatch and enqueue logic
- `*Fetcher` pagination and `FetcherFetchResult` specs
- `FlickrCatalogService` bulk persistence
- `FlickrRequestLimiter` permit logic (with Redis)
- `FlickrClientFactory` credential resolution
- Response helpers and outcome classification

Feature tests exercise:

- `FetchContactsPageJob` happy path and error handling
- Crawl type entry points (`CrawlTypesTest`)
- Target lifecycle and follow-up enqueue behavior

## Test doubles

The repository uses Mockery (`mockery/mockery`) for dependency isolation in unit tests. Prefer real Eloquent models and in-memory SQLite (Testbench default) for integration-style feature tests.

## Redis requirement

Rate limiter and dispatch feature tests require Redis. CI starts a Redis 7 service. Locally, start Redis before running the full suite:

```bash
composer test
```

If Redis is unavailable, feature tests that touch `FlickrRequestLimiter` or dispatch will fail.

## Coverage policy

| Metric | Target | Current (approx.) |
|--------|--------|-------------------|
| Line coverage | **95%** | **~78%** |

The maintainer target is 95% minimum line coverage. The repository is not yet at that gate — see [Risks, legacy, and gaps](../05-maintenance/01-risks-legacy-and-gaps.md).

When adding behavior, include focused unit or feature tests. Prefer covering under-tested areas:

- `FlickrSpiderService` edge paths (stall recovery, run completion)
- `XFlickrConfig` runtime override branches
- Gallery and photoset child-task chains in feature tests
- `FlickrApiOutcomeClassifier` rate-limit and auth branches

Do not lower the documented coverage target to match current numbers.

## Database safety

- Tests use Testbench migrations against isolated test databases.
- Never run `migrate:fresh` or destructive SQL against non-test databases without explicit approval.
- Do not point tests at production MySQL or Redis instances.

## CI behavior

`.github/workflows/ci.yml` runs `composer check` on push and pull request with PHP 8.5 and a Redis service.

## Related documents

- [Setup](./01-setup.md)
- [CI/CD](./03-ci-cd.md)
- [Risks, legacy, and gaps](../05-maintenance/01-risks-legacy-and-gaps.md)
