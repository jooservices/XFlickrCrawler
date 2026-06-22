# Architecture

## Flow

1. Host app calls `FlickrService::connection($key, $token)->contacts()` (or photos/photosets/galleries).
2. `CrawlingService` upserts `xflickr_connections`, creates `xflickr_crawl_runs`, enqueues page-1 targets.
3. `xflickr:dispatch` (every minute) locks due targets and pushes jobs to the `xflickr` queue.
4. Each `Fetch*PageJob` acquires a rate-limit permit, calls Flickr, audits the request, bulk-persists results, enqueues follow-up pages.

## Rate limiting

Per `connection_key`:

- Redis sliding window (`xflickr:req:{key}:window`)
- Minimum gap between requests (Lua on `xflickr:req:{key}:last`)
- Global pause on rate-limit responses (`xflickr:pause:{key}`)

## Persistence

- Contacts, photos, photosets, galleries stored in `xflickr_*` tables.
- Photo owners are bulk-upserted into contacts when photos are saved.
- Pivots use `insertOrIgnore` in chunks.
