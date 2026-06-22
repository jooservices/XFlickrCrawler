# XFlickr Crawler

Standalone Laravel 12 package for queued Flickr crawling with MySQL persistence, per-connection API rate limiting, and API call auditing.

## Requirements

- PHP 8.5+
- Laravel 12
- MySQL
- Redis (queues + rate limiting)
- Horizon or another queue worker (recommended)

## Installation

```bash
composer require jooservices/xflickr-crawler
composer require jooservices/laravel-config
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=xflickr-crawler-config
php artisan vendor:publish --tag=xflickr-crawler-host-integration
```

Run migrations:

```bash
php artisan migrate
```

**Host app checklist:** [docs/01-getting-started/host-app-integration.md](docs/01-getting-started/host-app-integration.md) — shared Redis, Horizon, scheduler, and `connection_key` strategy.

## Configuration

Register Flickr **app profiles** in `jooservices/laravel-config` (see [docs/01-getting-started/app-profiles.md](docs/01-getting-started/app-profiles.md)):

```php
use JOOservices\LaravelConfig\Facades\Config;

Config::set('xflickr_app.main', [
    'apiKey' => 'your-key',
    'apiSecret' => 'your-secret',
    'label' => 'Production',
], 'json');
```

Host `.env` (shared with app queue and Horizon):

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis

XFLICKR_DEFAULT_APP_PROFILE=main
XFLICKR_QUEUE=xflickr
XFLICKR_MAX_REQUESTS_PER_HOUR=3300
```

Runtime overrides (via `jooservices/laravel-config`):

- `xflickr.max_requests_per_hour` — per `connection_key`, not per subject NSID
- `xflickr.global_pause`
- `xflickr.dispatch_limit`
- `xflickr.default_app_profile`

## Usage

The package does **not** perform OAuth. Pass a **stable `connection_key`** (rate-limit scope), a JSON token payload, and the Flickr app profile:

```php
use JOOservices\XFlickrCrawler\Facades\FlickrService;

$connectionKey = 'user-42';
$token = json_encode([
    'oauthToken' => '...',
    'oauthTokenSecret' => '...',
    'userNsid' => '12037949629@N01',
]);

$conn = FlickrService::connection($connectionKey, $token, appProfile: 'main');

$run = $conn->contacts();
$run = $conn->photos('contact-nsid');
$run = $conn->photosets('contact-nsid');
$run = $conn->galleries('contact-nsid');
```

Each method returns a `CrawlRun` immediately. Work runs asynchronously on the `xflickr` queue via the host’s shared Horizon workers.

## Scheduler

```php
// routes/console.php
Schedule::command('xflickr:dispatch')->everyMinute();
```

## Horizon

Add the `xflickr` queue to a Horizon supervisor in the host app:

```php
'queue' => ['default', 'xflickr'],
```

Or use the published stub: `stubs/xflickr-horizon-supervisor.php`.

## Rate limiter state

```php
$state = FlickrService::limiterState('user-42');
// max_requests_per_hour, requests_used, requests_remaining, ...
```

## Tables

Package-owned tables use the `xflickr_*` prefix: connections, contacts, photos, photosets, galleries, pivots, crawl runs/targets, and API logs.

## Development

```bash
composer install
composer check      # lint + test
composer ci         # lint + test with coverage (CI)
```

## Documentation

| Audience | Start here |
|----------|------------|
| Install & config | [docs/README.md](docs/README.md) |
| Host app + shared Horizon | [docs/01-getting-started/host-app-integration.md](docs/01-getting-started/host-app-integration.md) |
| App profiles | [docs/01-getting-started/app-profiles.md](docs/01-getting-started/app-profiles.md) |
| Architecture | [docs/00-architecture/overview.md](docs/00-architecture/overview.md) |
| Contributors | [CONTRIBUTING.md](CONTRIBUTING.md) · [docs/04-development/03-ci-cd.md](docs/04-development/03-ci-cd.md) |
| AI agents | [AGENTS.md](AGENTS.md) · [ai/README.md](ai/README.md) |

## License

MIT — see [LICENSE](LICENSE).
