# Flickr app profiles

Flickr app credentials (`apiKey`, `apiSecret`) are stored as **named profiles** in `jooservices/laravel-config` (MongoDB). User OAuth tokens stay in MySQL on `xflickr_connections`. There is no `.env` fallback for app keys.

## Why both app credentials and user tokens?

Flickr uses OAuth 1.0a. Every REST call is signed with four secrets:

| Credential | Role | Stored in |
|------------|------|-----------|
| `apiKey` | Flickr application identity | `xflickr_app.{profile}` |
| `apiSecret` | Flickr application secret | `xflickr_app.{profile}` |
| `oauthToken` | User access token | `token_payload` on connection |
| `oauthTokenSecret` | User token secret | `token_payload` on connection |

The user token must have been issued by the **same Flickr app** as the profile credentials. If a user authorized App A but you sign with App B, Flickr returns an invalid signature.

## Register profiles (operator, one-time)

```php
use JOOservices\LaravelConfig\Facades\Config;

Config::set('xflickr_app.main', [
    'apiKey' => 'your-flickr-api-key',
    'apiSecret' => 'your-flickr-api-secret',
    'label' => 'Production',
], 'json');

Config::set('xflickr_app.agency', [
    'apiKey' => 'agency-key',
    'apiSecret' => 'agency-secret',
    'label' => 'Agency app',
], 'json');
```

Paths use `group.key` format only: `xflickr_app.main`, not nested paths like `xflickr_app.main.api_key`.

## Start a crawl (per user)

```php
use JOOservices\XFlickrCrawler\Facades\FlickrService;

$token = json_encode([
    'oauthToken' => 'user-oauth-token',
    'oauthTokenSecret' => 'user-oauth-secret',
    'userNsid' => '12037949629@N01',
]);

FlickrService::connection(
    connectionKey: '12037949629@N01',
    token: $token,
    appProfile: 'main',
)->contacts();
```

`appProfile` is optional. When omitted, the package uses `config('xflickr-crawler.default_app_profile')` or runtime `xflickr.default_app_profile`.

The package persists the link in MySQL:

| connection_key | app_profile | token_payload |
|----------------|-------------|---------------|
| `12037949629@N01` | `main` | user OAuth JSON |

## Queued jobs

Jobs only store `crawlTargetId`. At execution time they:

1. Load the crawl run’s `connection_key`
2. Read `app_profile` and `token_payload` from `xflickr_connections`
3. Load `xflickr_app.{app_profile}` from laravel-config
4. Build a signed Flickr client with both app and user credentials

You do not pass token or profile again when jobs run.

## Multi-app example

```php
FlickrService::connection('user-a', $tokenA, appProfile: 'main')->contacts();
FlickrService::connection('user-b', $tokenB, appProfile: 'agency')->contacts();
```

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `FlickrAppNotConfiguredException` for profile `main` | No `xflickr_app.main` in laravel-config | `Config::set('xflickr_app.main', ...)` |
| Invalid signature (Flickr 96) | Token from App A, profile uses App B | Match `appProfile` to the app that issued the token |
| `Flickr connection [x] was not found` | Job runs before connection row exists | Call `connection()` first to create the row |

## Security

- Do not log `apiSecret` or `token_payload`
- Restrict write access to `xflickr_app.*` to operators/admins
- App secrets in MongoDB (laravel-config); user tokens in MySQL
