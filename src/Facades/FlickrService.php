<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Facades;

use Illuminate\Support\Facades\Facade;
use JOOservices\XFlickrCrawler\FlickrConnection;
use JOOservices\XFlickrCrawler\FlickrCrawlerManager;

/**
 * @method static FlickrConnection connection(string $connectionKey, string $token, ?string $appProfile = null)
 * @method static array<string, mixed> limiterState(string $connectionKey)
 * @method static \JOOservices\XFlickrCrawler\Services\CrawlerCatalog catalog()
 * @method static \JOOservices\XFlickrCrawler\Services\CrawlerRuns runs()
 * @method static \JOOservices\XFlickrCrawler\Services\ConnectionRegistryService connections()
 *
 * @see FlickrCrawlerManager
 */
final class FlickrService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FlickrCrawlerManager::class;
    }
}
