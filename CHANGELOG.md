# Changelog

All notable changes to this package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-06-22

### Added

- **Favorites crawl** — `CrawlType::Favorites`, `FavoritesFetcher`, `FetchFavoritesPageJob`, `xflickr_favorites` table, and `FlickrConnection::favorites($subjectNsid)`
- **Connection-scoped contacts** — `xflickr_connection_contacts` pivot populated during contacts crawl
- **Read-model query API** — `CrawlerCatalog::countsForSubject()`, `contactsForConnection()`, `contactProfilesForConnection()`; `CrawlerRuns::activeForConnection()`, `recentForConnection()`
- **Domain events** — `CrawlRunCompleted` (when all targets finish), `CrawlPageFailed` (when a target fails)
- `FlickrCrawlerManager::catalog()` and `::runs()` accessors

### Changed

- `persistContacts()` accepts optional `connectionKey` to record per-connection contact discovery

[1.1.0]: https://github.com/jooservices/XFlickrCrawler/releases/tag/v1.1.0

## [1.0.0] - 2026-06-22

### Added

- Initial release of **XFlickr Crawler** (`jooservices/xflickr-crawler`)
- Queued Flickr crawling for contacts, photos, photosets, and galleries
- Per-connection Redis rate limiting with hourly window, minimum gap, and global cooldown
- MySQL persistence for catalog data, crawl runs/targets, and API audit logs
- Flickr app profile support via `jooservices/laravel-config` (`xflickr_app.{profile}`)
- Public API via `FlickrService::connection()` facade — no OAuth in package
- `xflickr:dispatch` scheduler command for target dispatch
- Bulk persistence through repositories (`upsertMany`, pivot `insertOrIgnore`)
- `FlickrPermitAcquirer` for rate-limit permit retry in queued jobs
- Host integration stubs (`vendor:publish --tag=xflickr-crawler-host-integration`)
- Optional `FlickrTransportContract` injection on `FlickrClientFactory` for testing
- PHPUnit test suite (60 tests) with `composer check` / `composer ci` quality gates
- Documentation hub, architecture, user guide, development, and maintenance docs
- AI skill pack under `.github/skills/` and `AGENTS.md` contributor policy

### Requirements

- PHP 8.5+
- Laravel 12
- MySQL, Redis
- `jooservices/flickr`, `jooservices/laravel-config`, `jooservices/dto`

[1.0.0]: https://github.com/jooservices/XFlickrCrawler/releases/tag/v1.0.0
