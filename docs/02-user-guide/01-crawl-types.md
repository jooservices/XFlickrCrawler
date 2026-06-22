# Crawl types

XFlickr Crawler supports four crawl types, each started through `FlickrService::connection()`:

```php
use JOOservices\XFlickrCrawler\Facades\FlickrService;

$connection = FlickrService::connection($connectionKey, $tokenJson, appProfile: 'main');

$run = $connection->contacts();
$run = $connection->photos($nsid);
$run = $connection->photosets($nsid);
$run = $connection->galleries($nsid);
```

Each method returns a `CrawlRun` immediately. Flickr API work runs asynchronously on the `xflickr` queue.

## Prerequisites

Before starting any crawl:

1. Register the Flickr app profile in laravel-config (`xflickr_app.{profile}`).
2. Pass a valid OAuth token JSON payload issued by that same app.
3. Ensure queue workers process the `xflickr` queue and `xflickr:dispatch` runs every minute.

See [Flickr app profiles](../01-getting-started/app-profiles.md) and [Installation](../01-getting-started/install.md).

## Contacts

```php
$run = FlickrService::connection($connectionKey, $token, appProfile: 'main')->contacts();
```

| Property | Value |
|----------|-------|
| `CrawlType` | `contacts` |
| Initial task | `ContactsPage` (page 1) |
| Flickr API | `flickr.contacts.getList` |
| Subject NSID | Not required (uses authenticated user) |
| Persisted data | `xflickr_contacts` |

Pagination: each page with more results enqueues another `ContactsPage` target. One queued job per page.

## Photos

```php
$run = FlickrService::connection($connectionKey, $token, appProfile: 'main')
    ->photos('contact-nsid');
```

| Property | Value |
|----------|-------|
| `CrawlType` | `photos` |
| Initial task | `PeoplePhotos` (page 1) |
| Flickr API | `flickr.people.getPhotos` |
| Subject NSID | Required — the Flickr user whose photos to crawl |
| Persisted data | `xflickr_photos`; photo owners bulk-upserted into `xflickr_contacts` |

Pagination: follow-up `PeoplePhotos` targets for subsequent pages.

## Photosets

```php
$run = FlickrService::connection($connectionKey, $token, appProfile: 'main')
    ->photosets('contact-nsid');
```

| Property | Value |
|----------|-------|
| `CrawlType` | `photosets` |
| Initial task | `PhotosetsList` (page 1) |
| Flickr API | `flickr.photosets.getList`, then `flickr.photosets.getPhotos` per set |
| Subject NSID | Required — owner of the photosets |
| Persisted data | `xflickr_photosets`, `xflickr_photos`, pivot rows |

Flow:

1. `PhotosetsList` fetches a page of photosets and enqueues `PhotosetsPhotos` for each set.
2. `PhotosetsPhotos` fetches photos within a set (with its own pagination).
3. Additional `PhotosetsList` targets handle list pagination.

## Galleries

```php
$run = FlickrService::connection($connectionKey, $token, appProfile: 'main')
    ->galleries('contact-nsid');
```

| Property | Value |
|----------|-------|
| `CrawlType` | `galleries` |
| Initial task | `GalleriesList` (page 1) |
| Flickr API | `flickr.galleries.getList`, then `flickr.galleries.getPhotos` per gallery |
| Subject NSID | Required — owner of the galleries |
| Persisted data | `xflickr_galleries`, `xflickr_photos`, pivot rows |

Flow mirrors photosets: list pages spawn child `GalleriesPhotos` tasks, each paginated independently.

## Monitoring a crawl

| Table | What to watch |
|-------|---------------|
| `xflickr_crawl_runs` | Run status, crawl type, subject NSID |
| `xflickr_crawl_targets` | Per-task status, page, retries, `next_run_at` |
| `xflickr_api_logs` | HTTP outcomes, latency, errors |

A run completes when all its targets reach a terminal state (`Completed` or `Failed`).

## Connection key

`connectionKey` scopes rate limiting and identifies the row in `xflickr_connections`. It is typically the authenticated user's NSID but can be any stable string your host app chooses, as long as it is consistent across calls for the same Flickr account.

## What this package does not do

- **OAuth** — obtain tokens in the host application; pass them as JSON to `connection()`.
- **Synchronous full crawls** — there is no API to block until all pages finish.
- **Cross-connection deduplication** — the same Flickr photo may be stored under multiple connections if crawled separately.

## Related documents

- [Data flow](../00-architecture/02-data-flow.md)
- [Rate limiting](./02-rate-limiting.md)
- [Flickr app profiles](../01-getting-started/app-profiles.md)
