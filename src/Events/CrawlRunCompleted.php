<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JOOservices\XFlickrCrawler\Models\CrawlRun;

final class CrawlRunCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly CrawlRun $run,
    ) {}
}
