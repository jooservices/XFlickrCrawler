<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\DTO;

use JOOservices\XFlickrCrawler\Enums\TaskType;

final class CrawlTaskSpec
{
    public function __construct(
        public readonly TaskType $taskType,
        public readonly ?string $subjectNsid = null,
        public readonly ?string $subjectId = null,
        public readonly int $page = 1,
        public readonly int $priority = 0,
    ) {}
}
