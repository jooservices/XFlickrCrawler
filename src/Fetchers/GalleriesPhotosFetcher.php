<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Fetchers;

use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\XFlickrCrawler\DTO\CrawlTaskSpec;
use JOOservices\XFlickrCrawler\DTO\FetcherFetchResult;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Models\CrawlTarget;
use JOOservices\XFlickrCrawler\Services\FlickrCatalogService;
use JOOservices\XFlickrCrawler\Support\FlickrResponseHelper;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

final class GalleriesPhotosFetcher
{
    public function __construct(
        private readonly FlickrCatalogService $catalog,
    ) {}

    public function fetchPage(CrawlTarget $target, ApiResponseData $response): FetcherFetchResult
    {
        $photos = FlickrResponseHelper::listItems($response->data, 'photos', 'photo');
        $ownerNsid = (string) ($target->subject_nsid ?? 'unknown');
        $galleryId = (string) $target->subject_id;
        $count = $this->catalog->persistGalleryPhotoPage($photos, $ownerNsid, $galleryId);

        $followUp = [];
        $pagination = $response->pagination;
        if ($pagination !== null && $pagination->page < $pagination->pages) {
            $followUp[] = new CrawlTaskSpec(
                taskType: TaskType::GalleriesPhotos,
                subjectNsid: $target->subject_nsid,
                subjectId: $target->subject_id,
                page: $pagination->page + 1,
            );
        }

        return new FetcherFetchResult(
            resultCount: $count,
            followUpSpecs: $followUp,
        );
    }

    public function perPage(): int
    {
        return XFlickrConfig::crawlInt('per_page', 500);
    }
}
