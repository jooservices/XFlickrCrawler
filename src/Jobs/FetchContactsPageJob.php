<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Jobs;

use JOOservices\XFlickrCrawler\Fetchers\ContactsFetcher;
use JOOservices\XFlickrCrawler\Services\FlickrApiAuditService;
use JOOservices\XFlickrCrawler\Services\FlickrApiOutcomeClassifier;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;
use JOOservices\XFlickrCrawler\Services\FlickrSpiderService;
use Throwable;

final class FetchContactsPageJob extends AbstractXFlickrCrawlJob
{
    public function handle(
        FlickrClientFactory $clients,
        FlickrRequestLimiter $limiter,
        FlickrApiOutcomeClassifier $classifier,
        FlickrApiAuditService $audit,
        FlickrSpiderService $spider,
        ContactsFetcher $fetcher,
    ): void {
        $target = $this->loadTarget();
        $connectionKey = $target !== null ? $this->connectionKey($target) : null;
        if ($target === null || $target->crawlRun === null || $connectionKey === null) {
            return;
        }

        $permit = $this->acquirePermit($limiter, $connectionKey);
        if (! $permit->acquired) {
            $this->releaseForPermit($target, $permit->retryAfterSeconds);

            return;
        }

        $apiMethod = 'flickr.contacts.getList';
        $started = hrtime(true);

        try {
            $client = $clients->forConnection($connectionKey);
            $response = $client->contacts()->getList([
                'per_page' => $fetcher->perPage(),
                'page' => $target->page,
            ]);
            $latencyMs = (int) ((hrtime(true) - $started) / 1_000_000);

            if (! $this->handleApiResponse($connectionKey, $response, $target, $apiMethod, $latencyMs, $classifier, $audit, $limiter)) {
                return;
            }

            $result = $fetcher->fetchPage($target, $response);
            $this->applyFollowUpSpecs($target->crawlRun, $result, $spider);
            $this->completeTarget($target, $spider, $result->resultCount);
        } catch (Throwable $throwable) {
            $this->handleThrowable($connectionKey, $throwable, $target, $apiMethod, $classifier, $audit, $limiter);
        }
    }
}
