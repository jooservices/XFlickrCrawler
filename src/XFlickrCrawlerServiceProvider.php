<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler;

use Illuminate\Support\ServiceProvider;
use JOOservices\XFlickrCrawler\Console\DispatchCrawlTargetsCommand;
use JOOservices\XFlickrCrawler\Repositories\ContactRepository;
use JOOservices\XFlickrCrawler\Repositories\GalleryRepository;
use JOOservices\XFlickrCrawler\Repositories\PhotoRepository;
use JOOservices\XFlickrCrawler\Repositories\PhotosetRepository;
use JOOservices\XFlickrCrawler\Repositories\PivotRepository;
use JOOservices\XFlickrCrawler\Services\CrawlingService;
use JOOservices\XFlickrCrawler\Services\FlickrApiAuditService;
use JOOservices\XFlickrCrawler\Services\FlickrApiOutcomeClassifier;
use JOOservices\XFlickrCrawler\Services\FlickrCatalogService;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Services\FlickrPermitAcquirer;
use JOOservices\XFlickrCrawler\Services\FlickrRequestLimiter;
use JOOservices\XFlickrCrawler\Services\FlickrSpiderService;

final class XFlickrCrawlerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/xflickr-crawler.php', 'xflickr-crawler');

        $this->app->singleton(FlickrCrawlerManager::class);
        $this->app->singleton(FlickrPermitAcquirer::class);
        $this->app->singleton(FlickrRequestLimiter::class);
        $this->app->singleton(FlickrApiOutcomeClassifier::class);
        $this->app->singleton(FlickrApiAuditService::class);
        $this->app->singleton(FlickrClientFactory::class);
        $this->app->singleton(FlickrSpiderService::class);
        $this->app->singleton(CrawlingService::class);
        $this->app->singleton(FlickrCatalogService::class);
        $this->app->singleton(ContactRepository::class);
        $this->app->singleton(PhotoRepository::class);
        $this->app->singleton(PhotosetRepository::class);
        $this->app->singleton(GalleryRepository::class);
        $this->app->singleton(PivotRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/xflickr-crawler.php' => config_path('xflickr-crawler.php'),
        ], 'xflickr-crawler-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'xflickr-crawler-migrations');

        $this->publishes([
            __DIR__.'/../stubs/host-integration/.env.xflickr.example' => base_path('.env.xflickr.example'),
            __DIR__.'/../stubs/host-integration/horizon-supervisor.php' => base_path('stubs/xflickr-horizon-supervisor.php'),
            __DIR__.'/../stubs/host-integration/console-schedule.php' => base_path('stubs/xflickr-console-schedule.php'),
            __DIR__.'/../stubs/host-integration/FlickrCrawlService.php.example' => app_path('Services/FlickrCrawlService.php.example'),
        ], 'xflickr-crawler-host-integration');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DispatchCrawlTargetsCommand::class,
            ]);
        }
    }
}
