# XFlickr Crawler Documentation

This documentation covers installation, crawl usage, architecture, and contributor workflow for **XFlickr Crawler**.

The Composer package name is `jooservices/xflickr-crawler`. The PHP namespace is `JOOservices\XFlickrCrawler\`.

## What the package does

XFlickr Crawler is a standalone Laravel 12 package for **queued Flickr crawling** with:

- per-connection API rate limiting (Redis)
- one queued job per Flickr API page
- MySQL persistence for contacts, photos, photosets, galleries, and crawl state
- API call auditing
- Flickr app credentials via `jooservices/laravel-config` profiles (`xflickr_app.{profile}`)

The package does **not** perform OAuth. The host application supplies user tokens and starts crawls through `FlickrService::connection()`.

## What to keep in mind

- Crawl entry is only through `FlickrService::connection($key, $token, ?$appProfile)` methods (`contacts`, `photos`, `photosets`, `galleries`).
- Never use `.env` `FLICKR_API_KEY` / `FLICKR_API_SECRET` in this package — app credentials live in laravel-config.
- Never loop Flickr API pages synchronously in services; pagination is always enqueued as follow-up targets.

Known gaps and coverage targets are documented in [Risks, Legacy, and Gaps](./05-maintenance/01-risks-legacy-and-gaps.md).

## Recommended reading order

1. [Installation](./01-getting-started/install.md)
2. [Host app integration](./01-getting-started/host-app-integration.md)
3. [Flickr app profiles](./01-getting-started/app-profiles.md)
4. [Architecture overview](./00-architecture/overview.md)
5. [Crawl types](./02-user-guide/01-crawl-types.md)
6. [Rate limiting](./02-user-guide/02-rate-limiting.md)

## Documentation map

### 00 — Architecture

Project design, crawl pipeline, module ownership, and runtime boundaries.

- [Overview](./00-architecture/overview.md)
- [Data flow](./00-architecture/02-data-flow.md)
- [Module map](./00-architecture/03-module-map.md)

### 01 — Getting started

Installation, configuration, and first crawl.

- [Installation](./01-getting-started/install.md)
- [Host app integration (shared Horizon)](./01-getting-started/host-app-integration.md)
- [Flickr app profiles](./01-getting-started/app-profiles.md)

### 02 — User guide

Feature usage for host applications integrating the crawler.

- [Crawl types](./02-user-guide/01-crawl-types.md)
- [Rate limiting](./02-user-guide/02-rate-limiting.md)

### 03 — Examples

Reserved for practical integration examples. None are published yet; see the [root README](../README.md) and getting-started docs for current usage.

### 04 — Development

Contributor workflow, local setup, testing, CI/CD, and AI skills.

- [Setup](./04-development/01-setup.md)
- [Testing](./04-development/02-testing.md)
- [CI/CD](./04-development/03-ci-cd.md)
- [AI skills](./04-development/04-ai-skills.md)
- [Contributing](./04-development/07-contributing.md)

### 05 — Maintenance

Known risks, coverage gaps, and maintainer notes.

- [Risks, legacy, and gaps](./05-maintenance/01-risks-legacy-and-gaps.md)

## Repository entry points

- [README](../README.md) — package overview and quick start
- [AGENTS.md](../AGENTS.md) — non-negotiable rules for AI and human contributors
- [CONTRIBUTING.md](../CONTRIBUTING.md) — short contributor pointer
- [CHANGELOG.md](../CHANGELOG.md) — release history

## Short example

```php
use JOOservices\XFlickrCrawler\Facades\FlickrService;

$connectionKey = 'user-42';
$token = json_encode([
    'oauthToken' => 'user-oauth-token',
    'oauthTokenSecret' => 'user-oauth-secret',
    'userNsid' => '12037949629@N01',
]);

$run = FlickrService::connection($connectionKey, $token, appProfile: 'main')->contacts();
```

Work runs asynchronously on the `xflickr` queue via the host’s shared Horizon. See [host-app-integration.md](./01-getting-started/host-app-integration.md).
