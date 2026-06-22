<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use Illuminate\Support\Facades\Redis;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class RateLimitTest extends TestCase
{
    private string $rateKey = 'rate-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiresRedis();

        $this->rateKey = 'rate-test-'.uniqid();

        Redis::del(
            "xflickr:req:{$this->rateKey}:window",
            "xflickr:req:{$this->rateKey}:last",
            "xflickr:pause:{$this->rateKey}",
        );

        config()->set('xflickr-crawler.throttle.max_requests_per_hour', 2);
        config()->set('xflickr-crawler.throttle.min_gap_ms', 0);
    }

    public function test_hourly_window_blocks_after_max_requests(): void
    {
        $limiter = app(FlickrRequestLimiter::class);
        $key = $this->rateKey;

        $this->assertTrue($limiter->acquire($key)->acquired);
        $this->assertTrue($limiter->acquire($key)->acquired);
        $this->assertFalse($limiter->acquire($key)->acquired);

        $state = $limiter->state($key);
        $this->assertSame(2, $state['requests_used']);
        $this->assertSame(0, $state['requests_remaining']);
    }

    public function test_global_cooldown_sets_pause_key(): void
    {
        $limiter = app(FlickrRequestLimiter::class);
        $key = 'cooldown-test-'.uniqid();

        $until = $limiter->triggerGlobalCooldown($key);

        $this->assertTrue($until->isFuture());
        $this->assertFalse($limiter->acquire($key)->acquired);

        Redis::del("xflickr:pause:{$key}");
    }
}
