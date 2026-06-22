<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Facades\FlickrService;
use JOOservices\XFlickrCrawler\Jobs\FetchGalleriesListJob;
use JOOservices\XFlickrCrawler\Jobs\FetchPeoplePhotosJob;
use JOOservices\XFlickrCrawler\Jobs\FetchPhotosetsListJob;
use JOOservices\XFlickrCrawler\Tests\Support\InMemoryConfigStore;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class CrawlTypesTest extends TestCase
{
    public function test_photos_starts_people_photos_target(): void
    {
        Queue::fake();

        $run = FlickrService::connection('acct-photos', $this->sampleToken())->photos('999@N01');

        $this->assertDatabaseHas('xflickr_crawl_targets', [
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::PeoplePhotos->value,
            'subject_nsid' => '999@N01',
        ]);

        Queue::assertPushed(FetchPeoplePhotosJob::class);
    }

    public function test_photosets_starts_list_target(): void
    {
        Queue::fake();

        $run = FlickrService::connection('acct-ps', $this->sampleToken())->photosets('888@N01');

        $this->assertDatabaseHas('xflickr_crawl_targets', [
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::PhotosetsList->value,
        ]);

        Queue::assertPushed(FetchPhotosetsListJob::class);
    }

    public function test_galleries_starts_list_target(): void
    {
        Queue::fake();

        $run = FlickrService::connection('acct-gal', $this->sampleToken())->galleries('777@N01');

        $this->assertDatabaseHas('xflickr_crawl_targets', [
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::GalleriesList->value,
        ]);

        Queue::assertPushed(FetchGalleriesListJob::class);
    }

    public function test_global_pause_blocks_crawl_start(): void
    {
        $this->app->make(InMemoryConfigStore::class)
            ->set('xflickr.global_pause', true, 'bool');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Global crawl pause');

        FlickrService::connection('paused', $this->sampleToken())->contacts();
    }
}
