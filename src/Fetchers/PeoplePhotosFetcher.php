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

final class PeoplePhotosFetcher
{
    public function __construct(
        private readonly FlickrCatalogService $catalog,
    ) {}

    public function fetchPage(CrawlTarget $target, ApiResponseData $response): FetcherFetchResult
    {
        $photos = FlickrResponseHelper::listItems($response->data, 'photos', 'photo');
        $subjectNsid = (string) $target->subject_nsid;
        $count = $this->catalog->persistPhotoPage($photos, $subjectNsid);

        $followUp = [];
        $pagination = $response->pagination;
        if ($pagination !== null && $pagination->page < $pagination->pages) {
            $followUp[] = new CrawlTaskSpec(
                taskType: TaskType::PeoplePhotos,
                subjectNsid: $subjectNsid,
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
