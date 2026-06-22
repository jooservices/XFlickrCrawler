<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Support;

use JOOservices\XFlickrCrawler\DTO\FlickrPermit;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;

final class StubFlickrPermitAcquirer
{
    public function __construct(
        private readonly FlickrPermit $permit,
    ) {}

    public function acquire(FlickrRequestLimiter $limiter, string $connectionKey, int $maxAttempts = 120): FlickrPermit
    {
        return $this->permit;
    }
}
