<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\XFlickrCrawler\Exceptions\FlickrAppNotConfiguredException;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FlickrClientFactoryTest extends TestCase
{
    public function test_for_connection_resolves_app_profile_from_connection_row(): void
    {
        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr_app.main', [
            'apiKey' => 'main-key',
            'apiSecret' => 'main-secret',
        ], 'json');

        Connection::query()->create([
            'connection_key' => 'acct-main',
            'app_profile' => 'main',
            'token_payload' => $this->sampleToken(),
        ]);

        $client = app(FlickrClientFactory::class)->forConnection('acct-main');

        $this->assertNotNull($client);
    }

    public function test_for_connection_throws_when_app_profile_not_configured(): void
    {
        Connection::query()->create([
            'connection_key' => 'acct-missing',
            'app_profile' => 'unknown',
            'token_payload' => $this->sampleToken(),
        ]);

        $this->expectException(FlickrAppNotConfiguredException::class);

        app(FlickrClientFactory::class)->forConnection('acct-missing');
    }
}
