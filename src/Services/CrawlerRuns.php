<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use Illuminate\Database\Eloquent\Collection;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Models\CrawlRun;

final class CrawlerRuns
{
    /**
     * @return Collection<int, CrawlRun>
     */
    public function activeForConnection(string $connectionKey): Collection
    {
        return CrawlRun::query()
            ->where('connection_key', $connectionKey)
            ->where('status', CrawlRunStatus::Running)
            ->with('targets')
            ->orderByDesc('started_at')
            ->get();
    }

    /**
     * @return Collection<int, CrawlRun>
     */
    public function recentForConnection(string $connectionKey, int $limit = 20): Collection
    {
        return CrawlRun::query()
            ->where('connection_key', $connectionKey)
            ->with('targets')
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }
}
