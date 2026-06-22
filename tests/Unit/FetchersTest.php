<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\Flickr\DTO\Common\PaginationData;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Fetchers\FavoritesFetcher;
use JOOservices\XFlickrCrawler\Fetchers\GalleriesListFetcher;
use JOOservices\XFlickrCrawler\Fetchers\GalleriesPhotosFetcher;
use JOOservices\XFlickrCrawler\Fetchers\PeoplePhotosFetcher;
use JOOservices\XFlickrCrawler\Fetchers\PhotosetsListFetcher;
use JOOservices\XFlickrCrawler\Fetchers\PhotosetsPhotosFetcher;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FetchersTest extends TestCase
{
    public function test_photosets_list_fetcher_schedules_photo_pages(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::PhotosetsList,
            'subject_nsid' => '111@N01',
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: ['photosets' => ['photoset' => [['id' => 'ps-1', 'title' => 'A']]]],
            pagination: new PaginationData(page: 1, pages: 1, perPage: 500, total: 1),
        );

        $result = app(PhotosetsListFetcher::class)->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertCount(1, $result->followUpSpecs);
        $this->assertSame(TaskType::PhotosetsPhotos, $result->followUpSpecs[0]->taskType);
    }

    public function test_photosets_photos_fetcher_paginates(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::PhotosetsPhotos,
            'subject_nsid' => '111@N01',
            'subject_id' => 'ps-1',
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: ['photoset' => ['photo' => [['id' => 'p-1', 'owner' => '111@N01']]]],
            pagination: new PaginationData(page: 1, pages: 2, perPage: 500, total: 2),
        );

        $result = app(PhotosetsPhotosFetcher::class)->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertCount(1, $result->followUpSpecs);
        $this->assertSame(2, $result->followUpSpecs[0]->page);
    }

    public function test_galleries_list_fetcher_schedules_photo_pages(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::GalleriesList,
            'subject_nsid' => '222@N01',
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: ['galleries' => ['gallery' => [['id' => 'gal-1', 'title' => 'G']]]],
            pagination: new PaginationData(page: 1, pages: 1, perPage: 500, total: 1),
        );

        $result = app(GalleriesListFetcher::class)->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertSame(TaskType::GalleriesPhotos, $result->followUpSpecs[0]->taskType);
    }

    public function test_galleries_photos_fetcher_paginates(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::GalleriesPhotos,
            'subject_nsid' => '222@N01',
            'subject_id' => 'gal-1',
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: ['gallery' => ['photo' => [['id' => 'gp-1', 'owner' => '222@N01']]]],
            pagination: new PaginationData(page: 1, pages: 3, perPage: 500, total: 3),
        );

        $result = app(GalleriesPhotosFetcher::class)->fetchPage($target, $response);

        $this->assertCount(1, $result->followUpSpecs);
        $this->assertSame(2, $result->followUpSpecs[0]->page);
    }

    public function test_people_photos_fetcher_paginates(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::PeoplePhotos,
            'subject_nsid' => '333@N01',
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: ['photos' => ['photo' => [['id' => 'pp-1', 'owner' => '333@N01']]]],
            pagination: new PaginationData(page: 1, pages: 2, perPage: 500, total: 2),
        );

        $result = app(PeoplePhotosFetcher::class)->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertSame(2, $result->followUpSpecs[0]->page);
    }

    public function test_favorites_fetcher_paginates(): void
    {
        $run = CrawlRun::query()->create([
            'connection_key' => 'fetcher-conn',
            'crawl_type' => 'favorites',
            'subject_nsid' => '444@N01',
            'status' => CrawlRunStatus::Running,
            'started_at' => now(),
        ]);

        $target = new CrawlTarget([
            'xflickr_crawl_run_id' => $run->id,
            'task_type' => TaskType::FavoritesPage,
            'subject_nsid' => '444@N01',
            'page' => 1,
        ]);
        $target->setRelation('crawlRun', $run);

        $response = new ApiResponseData(
            ok: true,
            data: ['photos' => ['photo' => [['id' => 'fp-1', 'owner' => 'owner@N01']]]],
            pagination: new PaginationData(page: 1, pages: 2, perPage: 500, total: 2),
        );

        $result = app(FavoritesFetcher::class)->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertCount(1, $result->followUpSpecs);
        $this->assertSame(TaskType::FavoritesPage, $result->followUpSpecs[0]->taskType);
        $this->assertSame(2, $result->followUpSpecs[0]->page);
    }
}
