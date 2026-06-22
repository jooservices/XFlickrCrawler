---
name: class-purpose-and-module-map
description: Module ownership map for jooservices/xflickr-crawler.
---

# Class purpose and module map

## Public API

| Class | Role |
|-------|------|
| `FlickrCrawlerManager` | Resolves connections and limiter state |
| `FlickrConnection` | Fluent entry: `contacts()`, `photos()`, `photosets()`, `galleries()` |
| `Facades\FlickrService` | Host-app facade |

## Orchestration

| Class | Role |
|-------|------|
| `Services\CrawlingService` | Creates crawl runs and page-1 targets |
| `Services\FlickrSpiderService` | Enqueue, dispatch, run completion |
| `Console\DispatchCrawlTargetsCommand` | Scheduler entry `xflickr:dispatch` |

## API + persistence

| Class | Role |
|-------|------|
| `Jobs\Fetch*PageJob` | One queued job per API page |
| `Fetchers\*Fetcher` | Parse response, bulk persist, pagination specs |
| `Services\FlickrCatalogService` | Bulk upsert catalog + pivots |
| `Repositories\*Repository` | `upsertMany`, `insertOrIgnore` |
| `Services\FlickrClientFactory` | Build signed Flickr client per connection |
| `Services\FlickrRequestLimiter` | Per-connection Redis rate limiting |
| `Services\FlickrPermitAcquirer` | Permit retry loop before API calls in jobs |
| `Services\FlickrApiAuditService` | API log rows + run counters |

## Config

| Class | Role |
|-------|------|
| `Support\XFlickrConfig` | Runtime config + app profile credentials |

## Rules

- Start crawls only through `FlickrService::connection()->*()`.
- Jobs store only `crawlTargetId`.
- Fetchers never call Flickr HTTP directly.
- No OAuth in this package.
