<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use JOOservices\XFlickrCrawler\DTO\CrawlTaskSpec;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlStatus;
use JOOservices\XFlickrCrawler\Enums\CrawlType;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Events\CrawlRunCompleted;
use JOOservices\XFlickrCrawler\Jobs\CrawlTargetJobFactory;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Models\Contact;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Models\Photo;
use JOOservices\XFlickrCrawler\Support\CrawlStall;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

final class FlickrSpiderService
{
    public function __construct(
        private readonly CrawlTargetJobFactory $jobFactory,
    ) {}

    public function dispatchDueTargets(): int
    {
        if (XFlickrConfig::globalPause()) {
            return 0;
        }

        $this->recoverStalledTargets();

        $limit = XFlickrConfig::dispatchLimit();
        $now = CarbonImmutable::now();

        $query = CrawlTarget::query()
            ->whereIn('status', [CrawlStatus::Pending, CrawlStatus::Queued])
            ->where(function ($query) use ($now): void {
                $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('locked_until')->orWhere('locked_until', '<=', $now);
            })
            ->orderByDesc('priority')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        /** @var Collection<int, CrawlTarget> $targets */
        $targets = $query->get();

        $dispatched = 0;
        foreach ($targets as $target) {
            if ($this->dispatchTarget($target)) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    public function recoverStalledTargets(): int
    {
        return CrawlTarget::query()
            ->where('status', CrawlStatus::Processing)
            ->where('updated_at', '<', CrawlStall::cutoff())
            ->update([
                'status' => CrawlStatus::Pending,
                'locked_until' => null,
                'next_run_at' => now(),
            ]);
    }

    public function enqueueTarget(
        CrawlRun $run,
        TaskType $taskType,
        ?string $subjectNsid,
        ?string $subjectId,
        int $page,
        int $priority = 0,
    ): CrawlTarget {
        return CrawlTarget::query()->firstOrCreate(
            [
                'xflickr_crawl_run_id' => $run->id,
                'task_type' => $taskType,
                'subject_nsid' => $subjectNsid,
                'subject_id' => $subjectId,
                'page' => $page,
            ],
            [
                'status' => CrawlStatus::Pending,
                'priority' => $priority,
                'next_run_at' => now(),
            ],
        );
    }

    /**
     * @param  list<CrawlTaskSpec>  $specs
     */
    public function enqueueSpecs(CrawlRun $run, array $specs): int
    {
        $count = 0;
        foreach ($specs as $spec) {
            $this->enqueueTarget(
                $run,
                $spec->taskType,
                $spec->subjectNsid,
                $spec->subjectId,
                $spec->page,
                $spec->priority,
            );
            $count++;
        }

        return $count;
    }

    public function createRun(
        string $connectionKey,
        CrawlType $crawlType,
        ?string $subjectNsid = null,
    ): CrawlRun {
        Connection::query()->updateOrCreate(
            ['connection_key' => $connectionKey],
            ['last_crawled_at' => now()],
        );

        return CrawlRun::query()->create([
            'connection_key' => $connectionKey,
            'crawl_type' => $crawlType->value,
            'subject_nsid' => $subjectNsid,
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function maybeCompleteRun(CrawlRun $run): void
    {
        $pending = CrawlTarget::query()
            ->where('xflickr_crawl_run_id', $run->id)
            ->whereNotIn('status', [CrawlStatus::Completed, CrawlStatus::Skipped, CrawlStatus::Failed])
            ->exists();

        if (! $pending && $run->status === CrawlRunStatus::Running) {
            $run->update([
                'status' => CrawlRunStatus::Completed,
                'completed_at' => now(),
            ]);

            $run->refresh();
            event(new CrawlRunCompleted($run));
        }
    }

    public function refreshRunCounters(CrawlRun $run): void
    {
        $run->update([
            'contacts_discovered' => (int) Contact::query()->count(),
            'photos_discovered' => (int) Photo::query()->count(),
        ]);
    }

    public function dispatchTarget(CrawlTarget $target): bool
    {
        $locked = CrawlTarget::query()
            ->whereKey($target->id)
            ->whereIn('status', [CrawlStatus::Pending, CrawlStatus::Queued])
            ->update([
                'status' => CrawlStatus::Queued,
                'locked_until' => now()->addMinutes(15),
            ]);

        if ($locked === 0) {
            return false;
        }

        $job = $this->jobFactory->make($target);

        dispatch($job)->onQueue((string) config('xflickr-crawler.queue', 'xflickr'));

        return true;
    }
}
