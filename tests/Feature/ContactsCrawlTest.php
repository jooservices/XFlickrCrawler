<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Facades\FlickrService;
use JOOservices\XFlickrCrawler\Jobs\FetchContactsPageJob;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class ContactsCrawlTest extends TestCase
{
    public function test_contacts_starts_crawl_run_and_enqueues_target(): void
    {
        Queue::fake();

        $run = FlickrService::connection('acct-1', $this->sampleToken())->contacts();

        $this->assertInstanceOf(CrawlRun::class, $run);
        $this->assertSame(CrawlRunStatus::Running, $run->status);
        $this->assertDatabaseHas('xflickr_connections', ['connection_key' => 'acct-1']);
        $this->assertDatabaseHas('xflickr_crawl_targets', [
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::ContactsPage->value,
            'page' => 1,
        ]);

        Queue::assertPushed(FetchContactsPageJob::class);
    }

    public function test_dispatch_command_dispatches_pending_targets(): void
    {
        Queue::fake();

        $run = CrawlRun::query()->create([
            'connection_key' => 'acct-2',
            'crawl_type' => 'contacts',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        Connection::query()->create([
            'connection_key' => 'acct-2',
            'app_profile' => 'default',
            'token_payload' => $this->sampleToken(),
        ]);

        CrawlTarget::query()->create([
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::ContactsPage,
            'page' => 1,
            'status' => CrawlStatus::Pending,
            'next_run_at' => now(),
        ]);

        $this->artisan('xflickr:dispatch')->assertSuccessful();

        Queue::assertPushed(FetchContactsPageJob::class);
    }
}
