<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\Flickr\DTO\Common\PaginationData;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Fetchers\ContactsFetcher;
use JOOservices\XFlickrCrawler\Models\Contact;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrCatalogService;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class ContactsFetcherTest extends TestCase
{
    public function test_persists_contacts_and_schedules_next_page(): void
    {
        $target = new CrawlTarget([
            'task_type' => TaskType::ContactsPage,
            'page' => 1,
        ]);

        $response = new ApiResponseData(
            ok: true,
            data: [
                'contacts' => [
                    'contact' => [
                        [
                            'nsid' => '111@N01',
                            'username' => 'alice',
                            'realname' => 'Alice',
                            'friend' => '1',
                            'family' => '0',
                        ],
                    ],
                ],
            ],
            pagination: new PaginationData(page: 1, pages: 2, perPage: 500, total: 2),
        );

        $fetcher = new ContactsFetcher(app(FlickrCatalogService::class));
        $result = $fetcher->fetchPage($target, $response);

        $this->assertSame(1, $result->resultCount);
        $this->assertCount(1, $result->followUpSpecs);
        $this->assertDatabaseHas('xflickr_contacts', [
            'nsid' => '111@N01',
            'username' => 'alice',
        ]);
    }

    public function test_catalog_bulk_upserts_photo_owners(): void
    {
        $catalog = app(FlickrCatalogService::class);
        $count = $catalog->persistPhotoPage([
            [
                'id' => 'photo-1',
                'owner' => '222@N01',
                'ownername' => 'bob',
                'title' => 'Sunset',
            ],
        ], '222@N01');

        $this->assertSame(1, $count);
        $this->assertSame(1, Contact::query()->where('nsid', '222@N01')->count());
    }
}
