<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Jobs\Concerns\InteractsWithXFlickrCrawlJob;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;

abstract class AbstractXFlickrCrawlJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithXFlickrCrawlJob;
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(
        protected readonly int $crawlTargetId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->crawlTargetId;
    }

    protected function loadTarget(): ?CrawlTarget
    {
        $target = CrawlTarget::query()
            ->with('crawlRun')
            ->find($this->crawlTargetId);

        if ($target === null) {
            return null;
        }

        if ($target->status === CrawlStatus::Completed) {
            return null;
        }

        $target->update([
            'status' => CrawlStatus::Processing,
            'locked_until' => now()->addMinutes(15),
        ]);

        return $target->fresh(['crawlRun']);
    }

    protected function connectionKey(CrawlTarget $target): ?string
    {
        return $target->crawlRun?->connection_key;
    }
}
