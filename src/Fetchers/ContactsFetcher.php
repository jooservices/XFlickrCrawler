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

final class ContactsFetcher
{
    public function __construct(
        private readonly FlickrCatalogService $catalog,
    ) {}

    public function fetchPage(CrawlTarget $target, ApiResponseData $response): FetcherFetchResult
    {
        $contacts = FlickrResponseHelper::listItems($response->data, 'contacts', 'contact');
        $count = $this->catalog->persistContacts($contacts);

        $followUp = [];
        $pagination = $response->pagination;
        if ($pagination !== null && $pagination->page < $pagination->pages) {
            $followUp[] = new CrawlTaskSpec(
                taskType: TaskType::ContactsPage,
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
