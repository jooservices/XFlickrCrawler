<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler;

use JOOservices\XFlickrCrawler\Services\CrawlerCatalog;
use JOOservices\XFlickrCrawler\Services\CrawlerRuns;
use JOOservices\XFlickrCrawler\Services\CrawlingService;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;

final class FlickrCrawlerManager
{
    public function __construct(
        private readonly CrawlingService $crawling,
        private readonly FlickrRequestLimiter $limiter,
        private readonly CrawlerCatalog $catalog,
        private readonly CrawlerRuns $runs,
    ) {}

    public function connection(string $connectionKey, string $token, ?string $appProfile = null): FlickrConnection
    {
        return new FlickrConnection($connectionKey, $token, $appProfile, $this->crawling);
    }

    /**
     * @return array<string, mixed>
     */
    public function limiterState(string $connectionKey): array
    {
        return $this->limiter->state($connectionKey);
    }

    public function catalog(): CrawlerCatalog
    {
        return $this->catalog;
    }

    public function runs(): CrawlerRuns
    {
        return $this->runs;
    }
}
