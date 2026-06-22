<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\XFlickrCrawler\Facades\FlickrService;
use JOOservices\XFlickrCrawler\FlickrConnection;
use JOOservices\XFlickrCrawler\FlickrCrawlerManager;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class ServiceProviderBindingTest extends TestCase
{
    public function test_facade_resolves_manager(): void
    {
        $manager = FlickrService::getFacadeRoot();

        $this->assertInstanceOf(FlickrCrawlerManager::class, $manager);
    }

    public function test_connection_returns_flickr_connection(): void
    {
        $connection = FlickrService::connection('test-nsid', $this->sampleToken());

        $this->assertInstanceOf(FlickrConnection::class, $connection);
        $this->assertSame('test-nsid', $connection->connectionKey());
    }
}
