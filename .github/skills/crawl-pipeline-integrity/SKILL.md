---
name: crawl-pipeline-integrity
description: Guardrails for XFlickr crawl queue, jobs, and target lifecycle.
---

# Crawl pipeline integrity

## Purpose

Keep the Flickr crawl queue, target lifecycle, and pagination follow-ups consistent and race-safe.

## When to use

- Editing jobs, fetchers, `FlickrSpiderService`, or `CrawlingService`
- Changing target status transitions or dispatch behavior

## Rules

- Enqueue via `FlickrSpiderService::enqueueTarget()` / `enqueueSpecs()`.
- Dispatch only through `FlickrSpiderService::dispatchTarget()` or `xflickr:dispatch`.
- Jobs store only `crawlTargetId` in the constructor.
- Fetchers return `FetcherFetchResult` with pagination follow-up `CrawlTaskSpec` entries.
- Never mark targets completed without a successful API response and persistence.
- Each Flickr API page is exactly one queued job — never loop pages synchronously in services.
- Every HTTP job must call `FlickrRequestLimiter::acquire($connectionKey)` before the API request.

## Validation checklist

- Target statuses transition: Pending → Queued → Processing → Completed|Failed|Pending (retry).
- Follow-up pages enqueue as new targets, not inline HTTP loops.
- `CrawlRun` completes only when all targets are terminal.
