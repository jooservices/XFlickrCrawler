<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use Illuminate\Support\Facades\Redis;
use JOOservices\Flickr\Client\FakeFlickrTransport;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FetchContactsPageJobErrorTest extends TestCase
{
    public function test_job_fails_target_on_api_error(): void
    {
        $run = CrawlRun::query()->create([
            'connection_key' => 'err-conn',
            'crawl_type' => 'contacts',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        Connection::query()->create([
            'connection_key' => 'err-conn',
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
            'stat' => 'fail',
            'code' => 1,
            'message' => 'Invalid signature',
        ]);

        $this->runContactsPageJob($target->id, clients: new FlickrClientFactory($transport));

        $target->refresh();
        $this->assertSame(CrawlStatus::Failed, $target->status);
        $this->assertStringContainsString('Invalid signature', (string) $target->failed_reason);
    }

    public function test_job_returns_early_when_target_missing(): void
    {
        $this->runContactsPageJob(999999);

        $this->assertTrue(true);
    }

    public function test_job_releases_target_when_permit_denied_without_calling_flickr(): void
    {
        $run = CrawlRun::query()->create([
            'connection_key' => 'denied-conn',
            'crawl_type' => 'contacts',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        Connection::query()->create([
            'connection_key' => 'denied-conn',
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
            'contacts' => ['page' => 1, 'pages' => 1, 'perpage' => 500, 'total' => 0],
        ]);

        $this->runContactsPageJob(
            $target->id,
            $this->deniedPermit(45),
            new FlickrClientFactory($transport),
        );

        $target->refresh();
        $this->assertSame(CrawlStatus::Pending, $target->status);
        $this->assertGreaterThan(0, $target->retry_count);
        $this->assertNotNull($target->next_run_at);
        $this->assertSame([], $transport->sentRequests());
    }

    public function test_classifier_rate_limit_releases_target_and_sets_cooldown(): void
    {
        $this->requiresRedis();

        $connectionKey = 'rate-conn-'.uniqid();
        $this->cleanLimiterKeys($connectionKey);

        $run = CrawlRun::query()->create([
            'connection_key' => $connectionKey,
            'crawl_type' => 'contacts',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        Connection::query()->create([
            'connection_key' => $connectionKey,
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
            'stat' => 'fail',
            'code' => 99,
            'message' => 'Rate limit exceeded',
        ]);

        $this->runContactsPageJob($target->id, clients: new FlickrClientFactory($transport));

        $target->refresh();
        $this->assertSame(CrawlStatus::Pending, $target->status);
        $this->assertGreaterThan(0, $target->retry_count);
        $this->assertNotNull(Redis::get("xflickr:pause:{$connectionKey}"));

        $this->cleanLimiterKeys($connectionKey);
    }
}
