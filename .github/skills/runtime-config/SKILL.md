---
name: runtime-config
description: Runtime configuration via jooservices/laravel-config for XFlickr Crawler.
---

# Runtime config

Read operator-tunable values through `XFlickrConfig`, not `env()` in package code.

## Crawl tuning keys

- `xflickr.max_requests_per_hour`
- `xflickr.global_pause`
- `xflickr.dispatch_limit`
- `xflickr.default_app_profile`

Static defaults live in `config/xflickr-crawler.php`.

## Flickr app profiles

App credentials resolve **only** from laravel-config profile JSON — no `.env` api keys.

- Path format: `xflickr_app.{slug}` (e.g. `xflickr_app.main`)
- JSON fields: `apiKey`, `apiSecret`, optional `label`
- Linked to connections via `app_profile` on `xflickr_connections`

See `docs/01-getting-started/app-profiles.md` for OAuth linking and troubleshooting.
