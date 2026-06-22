<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Support;

use Illuminate\Contracts\Foundation\Application;
use JOOservices\XFlickrCrawler\Repositories\ConnectionContactRepository;
use JOOservices\XFlickrCrawler\Repositories\ContactRepository;
use JOOservices\XFlickrCrawler\Repositories\FavoriteRepository;
use JOOservices\XFlickrCrawler\Repositories\GalleryRepository;
use JOOservices\XFlickrCrawler\Repositories\PhotoRepository;
use JOOservices\XFlickrCrawler\Repositories\PhotosetRepository;
use JOOservices\XFlickrCrawler\Repositories\PivotRepository;

final class RepositoryRegistrar
{
    /**
     * @param  Application  $app
     */
    public static function register(object $app): void
    {
        foreach ([
            ContactRepository::class,
            ConnectionContactRepository::class,
            FavoriteRepository::class,
            PhotoRepository::class,
            PhotosetRepository::class,
            GalleryRepository::class,
            PivotRepository::class,
        ] as $repository) {
            $app->singleton($repository);
        }
    }
}
