# Module map

This document maps `src/` directories and top-level classes to their responsibilities. Use it to find the right owner before editing behavior.

Namespace root: `JOOservices\XFlickrCrawler\`.

## Public API

| Location | Responsibility |
|----------|----------------|
| `FlickrCrawlerManager` | Factory for `FlickrConnection`; exposes `limiterState()` |
| `FlickrConnection` | Fluent crawl entry: `contacts()`, `photos()`, `photosets()`, `galleries()` |
| `Facades/FlickrService` | Laravel facade for `FlickrCrawlerManager` |
| `XFlickrCrawlerServiceProvider` | Service bindings, config publish, migrations, command registration |

**Rule:** Host apps start crawls only through `FlickrService::connection()` — not by calling services or jobs directly.

## Services

| Class | Responsibility |
|-------|----------------|
| `Services/CrawlingService` | Crawl orchestration: ensure connection, create run, enqueue page 1, trigger dispatch |
| `Services/FlickrSpiderService` | Target lifecycle: create runs, enqueue targets/specs, dispatch jobs, recover stalls, complete runs |
| `Services/FlickrRequestLimiter` | Per-connection Redis rate limiting and global cooldown |
| `Services/FlickrClientFactory` | Build signed `jooservices/flickr` clients from connection + laravel-config profile |
| `Services/FlickrCatalogService` | Bulk persistence orchestration across repositories |
| `Services/FlickrApiAuditService` | Write `xflickr_api_logs` rows |
| `Services/FlickrApiOutcomeClassifier` | Map API responses and exceptions to `ApiOutcome` |

## Jobs

| Class | Responsibility |
|-------|----------------|
| `Jobs/AbstractXFlickrCrawlJob` | Shared target loading, locking, uniqueness, queue config |
| `Jobs/Concerns/InteractsWithXFlickrCrawlJob` | Permit acquisition, outcome handling, target completion helpers |
| `Jobs/FetchContactsPageJob` | `TaskType::ContactsPage` HTTP + fetch |
| `Jobs/FetchPeoplePhotosJob` | `TaskType::PeoplePhotos` HTTP + fetch |
| `Jobs/FetchPhotosetsListJob` | `TaskType::PhotosetsList` HTTP + fetch |
| `Jobs/FetchPhotosetsPhotosJob` | `TaskType::PhotosetsPhotos` HTTP + fetch |
| `Jobs/FetchGalleriesListJob` | `TaskType::GalleriesList` HTTP + fetch |
| `Jobs/FetchGalleriesPhotosJob` | `TaskType::GalleriesPhotos` HTTP + fetch |

**Rule:** Jobs store only `crawlTargetId`. Credentials are resolved at execution time.

## Fetchers

| Class | Flickr data | Follow-up specs |
|-------|-------------|-----------------|
| `Fetchers/ContactsFetcher` | Contact list pages | Next `ContactsPage` |
| `Fetchers/PeoplePhotosFetcher` | Photos for an NSID | Next `PeoplePhotos` page |
| `Fetchers/PhotosetsListFetcher` | Photoset list | `PhotosetsPhotos` per set + list pagination |
| `Fetchers/PhotosetsPhotosFetcher` | Photos in a photoset | Pagination within set |
| `Fetchers/GalleriesListFetcher` | Gallery list | `GalleriesPhotos` per gallery + list pagination |
| `Fetchers/GalleriesPhotosFetcher` | Photos in a gallery | Pagination within gallery |

Fetchers parse API responses and return `FetcherFetchResult`; they do not enqueue jobs directly.

## Repositories

| Class | Tables |
|-------|--------|
| `Repositories/ContactRepository` | `xflickr_contacts` |
| `Repositories/PhotoRepository` | `xflickr_photos` |
| `Repositories/PhotosetRepository` | `xflickr_photosets` |
| `Repositories/GalleryRepository` | `xflickr_galleries` |
| `Repositories/PivotRepository` | Photo pivot tables |

**Rule:** Use `upsertMany()` and `insertOrIgnore()` — no per-row writes in fetchers.

## Models

| Class | Table | Role |
|-------|-------|------|
| `Models/Connection` | `xflickr_connections` | App profile + token payload per connection key |
| `Models/CrawlRun` | `xflickr_crawl_runs` | Top-level crawl session |
| `Models/CrawlTarget` | `xflickr_crawl_targets` | Single unit of queued work (one API page or child resource) |
| `Models/Contact` | `xflickr_contacts` | Flickr contact / user |
| `Models/Photo` | `xflickr_photos` | Photo metadata |
| `Models/Photoset` | `xflickr_photosets` | Photoset metadata |
| `Models/Gallery` | `xflickr_galleries` | Gallery metadata |
| `Models/ApiLog` | `xflickr_api_logs` | HTTP audit trail |

## DTOs

| Class | Role |
|-------|------|
| `DTO/CrawlTaskSpec` | Describes a target to enqueue (task type, nsid, foreign id, page) |
| `DTO/FetcherFetchResult` | Persisted count + follow-up `CrawlTaskSpec` list |
| `DTO/FlickrAppCredentialsDto` | Typed app credentials from laravel-config |
| `DTO/FlickrPermit` | Rate-limit acquisition result |

## Enums

| Enum | Values / role |
|------|---------------|
| `Enums/CrawlType` | `contacts`, `photos`, `photosets`, `galleries` — crawl run classification |
| `Enums/TaskType` | Page and child-resource task identifiers for targets and jobs |
| `Enums/CrawlStatus` | Target lifecycle states |
| `Enums/CrawlRunStatus` | Run lifecycle states |
| `Enums/ApiOutcome` | Classified API result for retry and cooldown decisions |

## Support

| Class | Role |
|-------|------|
| `Support/XFlickrConfig` | Package config + laravel-config runtime overrides |
| `Support/FlickrResponseHelper` | Parse Flickr list payloads from `ApiResponseData` |
| `Support/PeoplePhotosParams` | Parameter builder for people photos API calls |
| `Support/CrawlStall` | Stalled-target recovery cutoff |

## Console

| Class | Role |
|-------|------|
| `Console/DispatchCrawlTargetsCommand` | `xflickr:dispatch` — scheduled target dispatch |

## Exceptions

| Class | When thrown |
|-------|-------------|
| `Exceptions/FlickrAppNotConfiguredException` | `xflickr_app.{profile}` missing from laravel-config |

## Dependency boundaries

| Package | Used for |
|---------|----------|
| `jooservices/flickr` | Signed Flickr REST client and response DTOs |
| `jooservices/laravel-config` | Runtime config: app profiles, throttle overrides, global pause |
| `jooservices/dto` | Shared DTO patterns (transitive / internal DTOs) |
| Laravel queue + Redis | Job dispatch and rate limiting |
| MySQL | Catalog and crawl state persistence |

## Related documents

- [Data flow](./02-data-flow.md)
- [AGENTS.md](../../AGENTS.md)
- [.github/skills/class-purpose-and-module-map/SKILL.md](../../.github/skills/class-purpose-and-module-map/SKILL.md)
