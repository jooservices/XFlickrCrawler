# Getting started

## Install

```bash
composer require jooservices/xflickr-crawler
composer require jooservices/laravel-config
php artisan migrate
```

Install and configure `jooservices/laravel-config` in the host app (MongoDB-backed runtime config).

Optional publish:

```bash
php artisan vendor:publish --tag=xflickr-crawler-config
php artisan vendor:publish --tag=xflickr-crawler-host-integration
```

Full shared-Horizon checklist: [host-app-integration.md](host-app-integration.md).

## App profiles

Register Flickr app credentials in laravel-config — not in `.env`:

```php
use JOOservices\LaravelConfig\Facades\Config;

Config::set('xflickr_app.main', [
    'apiKey' => '...',
    'apiSecret' => '...',
], 'json');
```

See [app-profiles.md](app-profiles.md) for linking profiles to user tokens and connections.

## Env

Use the host app’s shared Redis for queues, cache, and rate limiting:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis

XFLICKR_QUEUE=xflickr
XFLICKR_DEFAULT_APP_PROFILE=main
```

See published `.env.xflickr.example` for all optional keys.

## Connection key

Use one stable `connection_key` per OAuth-linked account. Rate limits are per `connection_key`, not per `photos()` / `photosets()` / `galleries()` subject NSID. See [host-app-integration.md](host-app-integration.md#4-choose-connection_key-rate-limit-scope).

## Scheduler

```php
// routes/console.php
Schedule::command('xflickr:dispatch')->everyMinute();
```

## Horizon

Add the `xflickr` queue to the host’s `config/horizon.php`. See published `stubs/xflickr-horizon-supervisor.php`.

## First crawl

```php
use JOOservices\XFlickrCrawler\Facades\FlickrService;

$connectionKey = 'user-42';
$token = json_encode([/* oauthToken, oauthTokenSecret, userNsid */]);

FlickrService::connection($connectionKey, $token, appProfile: 'main')->contacts();
```

Monitor `xflickr_crawl_runs`, `xflickr_crawl_targets`, and `xflickr_api_logs`.
