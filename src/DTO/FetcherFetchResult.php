<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\DTO;

final class FetcherFetchResult
{
    /**
     * @param  list<CrawlTaskSpec>  $followUpSpecs
     */
    public function __construct(
        public readonly int $resultCount = 0,
        public readonly array $followUpSpecs = [],
    ) {}
}
