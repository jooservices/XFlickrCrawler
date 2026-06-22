<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Enums;

enum CrawlRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
