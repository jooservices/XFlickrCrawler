<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Jobs\Concerns;

use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\XFlickrCrawler\DTO\FetcherFetchResult;
use JOOservices\XFlickrCrawler\DTO\FlickrPermit;
use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Events\CrawlPageFailed;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrApiAuditService;
use JOOservices\XFlickrCrawler\Services\FlickrApiOutcomeClassifier;
use JOOservices\XFlickrCrawler\Services\FlickrPermitAcquirer;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;
use JOOservices\XFlickrCrawler\Services\FlickrSpiderService;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;
use Throwable;

trait InteractsWithXFlickrCrawlJob
{
    protected function acquirePermit(FlickrRequestLimiter $limiter, string $connectionKey, int $maxAttempts = 120): FlickrPermit
    {
        return app(FlickrPermitAcquirer::class)->acquire($limiter, $connectionKey, $maxAttempts);
    }

    protected function handleApiResponse(
        string $connectionKey,
        ApiResponseData $response,
        CrawlTarget $target,
        string $apiMethod,
        int $latencyMs,
        FlickrApiOutcomeClassifier $classifier,
        FlickrApiAuditService $audit,
        FlickrRequestLimiter $limiter,
    ): bool {
        $outcome = $classifier->fromApiResponse($response);

        $audit->log(
            connectionKey: $connectionKey,
            outcome: $outcome,
            apiMethod: $apiMethod,
            target: $target,
            latencyMs: $latencyMs,
            errorCode: $response->error?->code,
            errorMessage: $response->error?->message,
        );

        if ($target->crawlRun !== null) {
            $audit->incrementApiCalls($target->crawlRun);
        }

        if ($outcome === ApiOutcome::RateLimited) {
            $limiter->triggerGlobalCooldown($connectionKey);
            $this->releaseTarget($target, XFlickrConfig::throttle('rate_limit_backoff_seconds', 3600));

            return false;
        }

        if (! $response->ok) {
            $this->failTarget($target, $response->error !== null ? $response->error->message : 'Flickr API error');

            return false;
        }

        return true;
    }

    protected function handleThrowable(
        string $connectionKey,
        Throwable $throwable,
        CrawlTarget $target,
        string $apiMethod,
        FlickrApiOutcomeClassifier $classifier,
        FlickrApiAuditService $audit,
        FlickrRequestLimiter $limiter,
    ): void {
        $outcome = $classifier->fromThrowable($throwable);

        $audit->log(
            connectionKey: $connectionKey,
            outcome: $outcome,
            apiMethod: $apiMethod,
            target: $target,
            errorMessage: $throwable->getMessage(),
        );

        if ($outcome === ApiOutcome::RateLimited) {
            $limiter->triggerGlobalCooldown($connectionKey);
            $this->releaseTarget($target, XFlickrConfig::throttle('rate_limit_backoff_seconds', 3600));

            return;
        }

        $this->failTarget($target, $throwable->getMessage());
    }

    protected function releaseForPermit(CrawlTarget $target, int $seconds): void
    {
        $this->releaseTarget($target, $seconds);
        $this->release($seconds);
    }

    protected function applyFollowUpSpecs(
        CrawlRun $run,
        FetcherFetchResult $result,
        FlickrSpiderService $spider,
    ): void {
        $spider->enqueueSpecs($run, $result->followUpSpecs);
    }

    protected function completeTarget(CrawlTarget $target, FlickrSpiderService $spider, int $resultCount = 0): void
    {
        $target->update([
            'status' => CrawlStatus::Completed,
            'last_result_count' => $resultCount,
            'last_crawled_at' => now(),
            'locked_until' => null,
            'failed_reason' => null,
        ]);

        if ($target->crawlRun !== null) {
            $spider->maybeCompleteRun($target->crawlRun);
            $spider->refreshRunCounters($target->crawlRun);
        }
    }

    protected function releaseTarget(CrawlTarget $target, int $seconds): void
    {
        $target->update([
            'status' => CrawlStatus::Pending,
            'next_run_at' => now()->addSeconds($seconds),
            'locked_until' => null,
            'retry_count' => $target->retry_count + 1,
        ]);
    }

    protected function failTarget(CrawlTarget $target, string $reason): void
    {
        $target->update([
            'status' => CrawlStatus::Failed,
            'failed_reason' => $reason,
            'locked_until' => null,
            'last_crawled_at' => now(),
        ]);

        event(new CrawlPageFailed($target->fresh(['crawlRun']) ?? $target, $reason));
    }
}
