<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use JOOservices\Flickr\Client\FakeFlickrTransport;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FetchContactsPageJobTest extends TestCase
{
    public function test_job_fetches_contacts_and_completes_target(): void
    {
        $run = CrawlRun::query()->create([
            'connection_key' => 'job-conn',
            'crawl_type' => 'contacts',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        Connection::query()->create([
            'connection_key' => 'job-conn',
            'app_profile' => 'default',
            'token_payload' => $this->sampleToken(),
        ]);

        $target = CrawlTarget::query()->create([
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::ContactsPage,
            'page' => 1,
            'status' => CrawlStatus::Pending,
        ]);

        $transport = FakeFlickrTransport::new()->pushJson([
            'stat' => 'ok',
            'contacts' => [
                'page' => 1,
                'pages' => 1,
                'perpage' => 500,
                'total' => 1,
                'contact' => [
                    [
                        'nsid' => '999@N01',
                        'username' => 'job-user',
                        'friend' => 0,
                        'family' => 0,
                    ],
                ],
            ],
        ]);

        $clients = new FlickrClientFactory($transport);

        $this->runContactsPageJob($target->id, clients: $clients);

        $target->refresh();
        $this->assertSame(CrawlStatus::Completed, $target->status);
        $this->assertDatabaseHas('xflickr_contacts', ['nsid' => '999@N01']);
        $this->assertDatabaseHas('xflickr_api_logs', ['connection_key' => 'job-conn']);
    }
}
