<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\XFlickrCrawler\Exceptions\FlickrAppNotConfiguredException;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class XFlickrConfigAppCredentialsTest extends TestCase
{
    public function test_resolves_credentials_from_app_profile(): void
    {
        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr_app.main', [
            'apiKey' => 'profile-key',
            'apiSecret' => 'profile-secret',
            'label' => 'Production',
        ], 'json');

        $credentials = XFlickrConfig::appCredentials('main');

        $this->assertSame('profile-key', $credentials->apiKey);
        $this->assertSame('profile-secret', $credentials->apiSecret);
        $this->assertSame('Production', $credentials->label);
    }

    public function test_throws_when_profile_is_missing(): void
    {
        $this->expectException(FlickrAppNotConfiguredException::class);
        $this->expectExceptionMessage('xflickr_app.missing');

        XFlickrConfig::appCredentials('missing');
    }

    public function test_throws_when_credentials_are_incomplete(): void
    {
        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr_app.incomplete', ['apiKey' => 'only-key'], 'json');

        $this->expectException(FlickrAppNotConfiguredException::class);

        XFlickrConfig::appCredentials('incomplete');
    }

    public function test_sanitize_profile_slug_rejects_invalid_values(): void
    {
        $this->expectException(FlickrAppNotConfiguredException::class);

        XFlickrConfig::sanitizeProfileSlug('bad profile!');
    }

    public function test_default_app_profile_reads_static_config(): void
    {
        config(['xflickr-crawler.default_app_profile' => 'agency']);

        $this->assertSame('agency', XFlickrConfig::defaultAppProfile());
    }

    public function test_default_app_profile_prefers_runtime_override(): void
    {
        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr.default_app_profile', 'runtime-main');

        $this->assertSame('runtime-main', XFlickrConfig::defaultAppProfile());
    }
}
