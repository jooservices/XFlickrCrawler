---
name: crawl-pipeline-integrity
description: Guardrails for XFlickr crawl queue, jobs, and target lifecycle.
---

# Crawl pipeline integrity

- Enqueue via `FlickrSpiderService::enqueueTarget()` / `enqueueSpecs()`.
- Dispatch only through `FlickrSpiderService::dispatchTarget()` or `xflickr:dispatch`.
- Jobs store only `crawlTargetId` in the constructor.
- Fetchers return `FetcherFetchResult` with pagination follow-up `CrawlTaskSpec` entries.
- Never mark targets completed without a successful API response and persistence.
