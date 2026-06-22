---
name: testing-and-quality-gates
description: Testing and lint requirements for XFlickr Crawler.
---

# Testing & quality gates

Run before merge:

```bash
composer check
```

- PHPUnit: provider bindings, fetcher persistence, crawl enqueue, rate limiter (Redis required for limiter feature tests).
- Pint + PHPStan + PHPCS + PHPMD via `composer lint:all`.

Use `RefreshDatabase` for persistence tests. Mock Flickr HTTP at the job layer when testing full crawl flows.
