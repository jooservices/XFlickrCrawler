<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;

final class CrawlPageFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly CrawlTarget $target,
        public readonly string $reason,
    ) {}
}
