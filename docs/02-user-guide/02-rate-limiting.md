# Rate limiting

XFlickr Crawler enforces Flickr API rate limits per **connection key** using Redis. Every queued HTTP job must acquire a permit before calling the API.

## Non-negotiable rule

```php
// Inside every Fetch*PageJob, before the HTTP call:
$permit = $limiter->acquire($connectionKey);
```

If acquisition fails, the job must not call Flickr. It releases the target with a `next_run_at` backoff instead.

This is enforced in `Jobs/Concerns/InteractsWithXFlickrCrawlJob` and documented in [AGENTS.md](../../AGENTS.md).

## How it works

`FlickrRequestLimiter` applies three layers per `connection_key`:

### 1. Sliding hourly window

Redis sorted set `xflickr:req:{connectionKey}:window` tracks request timestamps within a configurable window (default one hour).

- Maximum requests per window: `xflickr.max_requests_per_hour` (default 3300, overridable via laravel-config).
- When the window is full, `acquire()` returns a denied `FlickrPermit` with retry seconds.

### 2. Minimum gap between requests

Redis key `xflickr:req:{connectionKey}:last` enforces a minimum milliseconds gap between consecutive requests (default 333 ms via `min_gap_ms`).

Implemented with a Lua script to avoid races between concurrent workers.

### 3. Global cooldown on rate-limit responses

When Flickr returns a rate-limit outcome, `triggerGlobalCooldown()` sets `xflickr:pause:{connectionKey}` for `rate_limit_backoff_seconds` (default 3600).

While paused, all `acquire()` calls for that connection are denied until the pause expires.

## Global pause (operator)

Operators can halt all crawling without redeploying:

```php
use JOOservices\LaravelConfig\Facades\Config;

Config::set('xflickr.global_pause', true, 'bool');
```

When active:

- `CrawlingService` refuses new crawl starts.
- `FlickrSpiderService::dispatchDueTargets()` returns 0.
- `FlickrRequestLimiter::acquire()` denies immediately.

Clear the flag to resume.

## Runtime configuration

Throttle values can be overridden through `jooservices/laravel-config`:

| Key | Purpose | Default |
|-----|---------|---------|
| `xflickr.max_requests_per_hour` | Hourly request cap | 3300 |
| `xflickr.global_pause` | Halt all crawl activity | `false` |
| `xflickr.dispatch_limit` | Max targets dispatched per `xflickr:dispatch` tick | package config |
| `xflickr.default_app_profile` | Fallback app profile slug | package config |

Package config file (`config/xflickr-crawler.php`) provides static defaults; laravel-config overrides apply at runtime.

## Inspecting limiter state

```php
$state = FlickrService::limiterState('12037949629@N01');

// Returns:
// - connection_key
// - max_requests_per_hour
// - requests_used
// - requests_remaining
// - min_gap_ms
// - global_pause
// - global_pause_until
// - global_pause_seconds_remaining
```

Use this for dashboards or operator tooling in the host application.

## Queue interaction

Rate limiting is separate from Laravel queue concurrency:

- Multiple workers may dequeue jobs for the same connection simultaneously.
- Only jobs that acquire a permit proceed to HTTP; others reschedule their target.
- Jobs implement `ShouldBeUnique` per `crawlTargetId` to prevent duplicate page processing.

Ensure Redis is available to both queue workers and the web/scheduler processes that start crawls.

## Flickr rate-limit response handling

`FlickrApiOutcomeClassifier` maps Flickr error codes to `ApiOutcome::RateLimited`. On rate limit:

1. `triggerGlobalCooldown()` pauses the connection key.
2. The target is released for retry after the cooldown.
3. The attempt is recorded in `xflickr_api_logs`.

Do not bypass the cooldown by calling Flickr directly from the host app using the same connection key.

## Related documents

- [Architecture overview](../00-architecture/overview.md)
- [Data flow](../00-architecture/02-data-flow.md)
- [Installation](../01-getting-started/install.md)
