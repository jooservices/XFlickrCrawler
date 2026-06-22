<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\FlickrConnection;
use JOOservices\XFlickrCrawler\Services\CrawlingService;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FlickrConnectionTest extends TestCase
{
    public function test_connection_key_accessor(): void
    {
        $connection = new FlickrConnection(
            'key-1',
            $this->sampleToken(),
            'main',
            app(CrawlingService::class),
        );

        $this->assertSame('key-1', $connection->connectionKey());
    }

    public function test_initial_contacts_specs(): void
    {
        $specs = app(CrawlingService::class)->initialContactsSpecs();

        $this->assertCount(1, $specs);
        $this->assertSame(TaskType::ContactsPage, $specs[0]->taskType);
        $this->assertSame(1, $specs[0]->page);
    }
}
