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

final class GalleriesListFetcher
{
    public function __construct(
        private readonly FlickrCatalogService $catalog,
    ) {}

    public function fetchPage(CrawlTarget $target, ApiResponseData $response): FetcherFetchResult
    {
        $galleries = FlickrResponseHelper::listItems($response->data, 'galleries', 'gallery');
        $ownerNsid = (string) ($target->subject_nsid ?? 'unknown');
        $count = $this->catalog->persistGalleries($galleries, $ownerNsid);

        $followUp = [];
        foreach ($galleries as $galleryData) {
            $galleryId = (string) ($galleryData['id'] ?? '');
            if ($galleryId === '') {
                continue;
            }

            $followUp[] = new CrawlTaskSpec(
                taskType: TaskType::GalleriesPhotos,
                subjectNsid: $ownerNsid,
                subjectId: $galleryId,
            );
        }

        $pagination = $response->pagination;
        if ($pagination !== null && $pagination->page < $pagination->pages) {
            $followUp[] = new CrawlTaskSpec(
                taskType: TaskType::GalleriesList,
                subjectNsid: $target->subject_nsid,
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
