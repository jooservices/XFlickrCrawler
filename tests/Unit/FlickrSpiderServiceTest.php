<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlType;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrApiAuditService;
use JOOservices\XFlickrCrawler\Services\FlickrSpiderService;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FlickrSpiderServiceTest extends TestCase
{
    public function test_enqueue_specs_and_complete_run(): void
    {
        $spider = app(FlickrSpiderService::class);

        $run = CrawlRun::query()->create([
            'connection_key' => 'spider-1',
            'crawl_type' => CrawlType::Contacts->value,
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        $target = $spider->enqueueTarget($run, TaskType::ContactsPage, null, null, 1);
        $this->assertSame(CrawlStatus::Pending, $target->status);

        $target->update(['status' => CrawlStatus::Completed]);
        $spider->maybeCompleteRun($run->fresh());

        $this->assertSame(CrawlRunStatus::Completed, $run->fresh()->status);
    }

    public function test_maybe_complete_run_when_all_targets_failed(): void
    {
        $spider = app(FlickrSpiderService::class);

        $run = CrawlRun::query()->create([
            'connection_key' => 'spider-failed',
            'crawl_type' => CrawlType::Photosets->value,
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        CrawlTarget::query()->create([
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::PhotosetsList,
            'subject_nsid' => '999@N01',
            'page' => 1,
            'status' => CrawlStatus::Failed,
        ]);

        $spider->maybeCompleteRun($run->fresh());

        $this->assertSame(CrawlRunStatus::Completed, $run->fresh()->status);
    }

    public function test_audit_service_logs_and_increments(): void
    {
        $audit = app(FlickrApiAuditService::class);
        $run = CrawlRun::query()->create([
            'connection_key' => 'audit-1',
            'crawl_type' => CrawlType::Contacts->value,
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        $target = CrawlTarget::query()->create([
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::ContactsPage,
            'page' => 1,
            'status' => CrawlStatus::Pending,
        ]);

        $log = $audit->log('audit-1', ApiOutcome::Success, 'flickr.contacts.getList', $target, 12);
        $audit->incrementApiCalls($run);

        $this->assertDatabaseHas('xflickr_api_logs', [
            'id' => $log->id,
            'api_method' => 'flickr.contacts.getList',
        ]);
        $this->assertSame(1, $run->fresh()->api_calls);
    }

    public function test_dispatch_due_targets_returns_zero_when_paused(): void
    {
        $store = $this->app->make(InMemoryConfigStore::class);
        $store->set('xflickr.global_pause', true);

        $spider = app(FlickrSpiderService::class);
        $this->assertSame(0, $spider->dispatchDueTargets());
    }
}
