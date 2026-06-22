<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use JOOservices\XFlickrCrawler\Models\ApiLog;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;

final class FlickrApiAuditService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function log(
        string $connectionKey,
        ApiOutcome $outcome,
        string $apiMethod,
        ?CrawlTarget $target = null,
        ?int $latencyMs = null,
        ?int $errorCode = null,
        ?string $errorMessage = null,
        array $context = [],
    ): ApiLog {
        return ApiLog::query()->create([
            'connection_key' => $connectionKey,
            'xflickr_crawl_run_id' => $target?->xflickr_crawl_run_id,
            'xflickr_crawl_target_id' => $target?->id,
            'api_method' => $apiMethod,
            'outcome' => $outcome,
            'latency_ms' => $latencyMs,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'context' => $context === [] ? null : $context,
            'created_at' => now(),
        ]);
    }

    public function incrementApiCalls(CrawlRun $run): void
    {
        CrawlRun::query()
            ->whereKey($run->id)
            ->increment('api_calls');
    }
}
