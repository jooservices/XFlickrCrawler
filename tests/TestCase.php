<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use JOOservices\XFlickrCrawler\DTO\FlickrPermit;
use JOOservices\XFlickrCrawler\Fetchers\ContactsFetcher;
use JOOservices\XFlickrCrawler\Jobs\FetchContactsPageJob;
use JOOservices\XFlickrCrawler\Services\FlickrApiAuditService;
use JOOservices\XFlickrCrawler\Services\FlickrApiOutcomeClassifier;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Services\FlickrPermitAcquirer;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;
use JOOservices\XFlickrCrawler\Services\FlickrSpiderService;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\Support\StubFlickrPermitAcquirer;
use JOOservices\XFlickrCrawler\XFlickrCrawlerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            XFlickrCrawlerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:3n8+o8v8LxQ5p5QnMei8zyfY4m4Q7vHf+9v1W8o5H9Q=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('xflickr-crawler.default_app_profile', 'default');
        $app['config']->set('xflickr-crawler.throttle.max_requests_per_hour', 10);
        $app['config']->set('xflickr-crawler.throttle.min_gap_ms', 0);
        $app['config']->set('xflickr-crawler.crawl.dispatch_limit', 5);
        $app['config']->set('database.redis.client', 'phpredis');
        $app['config']->set('database.redis.default', [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => (int) env('REDIS_PORT', 6379),
            'database' => (int) env('REDIS_DB', 15),
        ]);

        $configStore = new InMemoryConfigStore;
        $configStore->set('xflickr_app.default', [
            'apiKey' => 'test-api-key',
            'apiSecret' => 'test-api-secret',
        ], 'json');
        $app->instance('config-store', $configStore);
        $app->instance(InMemoryConfigStore::class, $configStore);
    }

    protected function requiresRedis(): void
    {
        try {
            Redis::connection()->ping();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('Redis is required for this test: '.$exception->getMessage());
        }
    }

    protected function sampleToken(): string
    {
        return json_encode([
            'oauthToken' => 'token',
            'oauthTokenSecret' => 'secret',
            'userNsid' => '12037949629@N01',
        ], JSON_THROW_ON_ERROR);
    }

    protected function grantedPermit(): FlickrPermit
    {
        return new FlickrPermit(true, 0);
    }

    protected function deniedPermit(int $retryAfterSeconds = 60): FlickrPermit
    {
        return new FlickrPermit(false, $retryAfterSeconds);
    }

    protected function cleanLimiterKeys(string ...$connectionKeys): void
    {
        foreach ($connectionKeys as $connectionKey) {
            Redis::del(
                "xflickr:req:{$connectionKey}:window",
                "xflickr:req:{$connectionKey}:last",
                "xflickr:pause:{$connectionKey}",
            );
        }
    }

    protected function runContactsPageJob(
        int $targetId,
        ?FlickrPermit $permitOverride = null,
        ?FlickrClientFactory $clients = null,
        bool $useRealPermitAcquirer = false,
    ): void {
        if ($useRealPermitAcquirer) {
            $this->app->instance(FlickrPermitAcquirer::class, new FlickrPermitAcquirer);
        } else {
            $this->app->instance(
                FlickrPermitAcquirer::class,
                new StubFlickrPermitAcquirer($permitOverride ?? $this->grantedPermit()),
            );
        }

        (new FetchContactsPageJob($targetId))->handle(
            $clients ?? app(FlickrClientFactory::class),
            app(FlickrRequestLimiter::class),
            app(FlickrApiOutcomeClassifier::class),
            app(FlickrApiAuditService::class),
            app(FlickrSpiderService::class),
            app(ContactsFetcher::class),
        );
    }
}
