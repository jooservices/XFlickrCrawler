# Setup

## Prerequisites

- PHP `>=8.5`
- Composer 2.x
- Git
- MySQL (catalog and crawl state tables)
- Redis (queues and rate limiting)
- Optional: [gitleaks](https://github.com/gitleaks/gitleaks) if CaptainHook secret scanning is enabled locally

## Clone and install

```bash
git clone https://github.com/jooservices/xflickr-crawler.git
cd xflickr-crawler
composer install
```

`composer install` runs CaptainHook setup via `post-install-cmd` / `post-update-cmd` when `captainhook/captainhook` is present.

## Path repositories (local development)

`composer.json` may reference sibling packages for local work:

```json
"repositories": [
    { "type": "path", "url": "../flickr" },
    { "type": "path", "url": "../laravel-config" }
]
```

Adjust paths to match your workspace layout before `composer install`.

## Verify local commands

```bash
composer test
composer lint
composer lint:all
composer check
```

`composer check` is the repository quality gate: `lint:all` (Pint, PHPCS, PHPStan, PHPMD) plus `test`.

## Test environment

PHPUnit uses Orchestra Testbench. Feature tests require Redis (rate limiter and queue interactions). CI provides a Redis 7 service container.

For local feature tests, ensure Redis is running on the default connection used by `phpunit.xml` / Testbench configuration.

## CaptainHook

If hooks are missing after install:

```bash
vendor/bin/captainhook install --force --skip-existing
```

CaptainHook may enforce:

- Conventional Commit message format
- Pre-commit lint or secret scans (when configured)

## IDE and static analysis

Configuration files in the repository root:

| File | Tool |
|------|------|
| `phpstan.neon` | PHPStan / Larastan |
| `phpcs.xml` | PHP_CodeSniffer |
| `phpmd.xml` | PHPMD |
| `pint.json` or default Pint rules | Laravel Pint |

Run `composer lint:pint:fix` before commit when formatting drifts.

## Host application integration (smoke test)

See [Host app integration](../01-getting-started/host-app-integration.md) for the full checklist.

1. `composer require jooservices/xflickr-crawler`
2. Install and configure `jooservices/laravel-config`
3. `php artisan migrate`
4. `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `XFLICKR_QUEUE=xflickr`
5. Register `xflickr_app.{profile}` credentials
6. Add `xflickr` queue to host `config/horizon.php`
7. Schedule `xflickr:dispatch` every minute
8. Use one stable `connection_key` per OAuth account when calling `FlickrService`

## Related documents

- [Testing](./02-testing.md)
- [CI/CD](./03-ci-cd.md)
- [Contributing](./07-contributing.md)
