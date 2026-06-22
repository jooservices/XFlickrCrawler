<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler;

use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Services\CrawlingService;

final class FlickrConnection
{
    public function __construct(
        private readonly string $connectionKey,
        private readonly string $tokenPayload,
        private readonly ?string $appProfile,
        private readonly CrawlingService $crawling,
    ) {}

    public function contacts(): CrawlRun
    {
        return $this->crawling->startContacts($this->connectionKey, $this->tokenPayload, $this->appProfile);
    }

    public function photos(string $nsid): CrawlRun
    {
        return $this->crawling->startPhotos($this->connectionKey, $this->tokenPayload, $nsid, $this->appProfile);
    }

    public function photosets(string $nsid): CrawlRun
    {
        return $this->crawling->startPhotosets($this->connectionKey, $this->tokenPayload, $nsid, $this->appProfile);
    }

    public function galleries(string $nsid): CrawlRun
    {
        return $this->crawling->startGalleries($this->connectionKey, $this->tokenPayload, $nsid, $this->appProfile);
    }

    public function connectionKey(): string
    {
        return $this->connectionKey;
    }
}
