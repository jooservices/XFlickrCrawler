<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use JOOservices\XFlickrCrawler\Facades\FlickrService;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class AppProfilePersistenceTest extends TestCase
{
    public function test_connection_persists_explicit_app_profile(): void
    {
        Queue::fake();

        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr_app.main', [
            'apiKey' => 'main-key',
            'apiSecret' => 'main-secret',
        ], 'json');

        FlickrService::connection('acct-profile', $this->sampleToken(), appProfile: 'main')->contacts();

        $this->assertDatabaseHas('xflickr_connections', [
            'connection_key' => 'acct-profile',
            'app_profile' => 'main',
        ]);
    }

    public function test_connection_uses_default_app_profile_when_omitted(): void
    {
        Queue::fake();

        FlickrService::connection('acct-default', $this->sampleToken())->contacts();

        $this->assertDatabaseHas('xflickr_connections', [
            'connection_key' => 'acct-default',
            'app_profile' => 'default',
        ]);
    }
}
