<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use JOOservices\XFlickrCrawler\DTO\CrawlTaskSpec;
use JOOservices\XFlickrCrawler\Enums\CrawlType;
use JOOservices\XFlickrCrawler\Enums\TaskType;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Models\CrawlRun;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;
use RuntimeException;

final class CrawlingService
{
    public function __construct(
        private readonly FlickrSpiderService $spider,
    ) {}

    public function startContacts(string $connectionKey, string $tokenPayload, ?string $appProfile = null): CrawlRun
    {
        $this->ensureConnection($connectionKey, $tokenPayload, $appProfile);
        $this->guardGlobalPause();

        $run = $this->spider->createRun($connectionKey, CrawlType::Contacts);
        $this->spider->enqueueTarget($run, TaskType::ContactsPage, null, null, 1);
        $this->spider->dispatchDueTargets();

        return $run;
    }

    public function startPhotos(string $connectionKey, string $tokenPayload, string $nsid, ?string $appProfile = null): CrawlRun
    {
        $this->ensureConnection($connectionKey, $tokenPayload, $appProfile);
        $this->guardGlobalPause();

        $run = $this->spider->createRun($connectionKey, CrawlType::Photos, $nsid);
        $this->spider->enqueueTarget($run, TaskType::PeoplePhotos, $nsid, null, 1);
        $this->spider->dispatchDueTargets();

        return $run;
    }

    public function startPhotosets(string $connectionKey, string $tokenPayload, string $nsid, ?string $appProfile = null): CrawlRun
    {
        $this->ensureConnection($connectionKey, $tokenPayload, $appProfile);
        $this->guardGlobalPause();

        $run = $this->spider->createRun($connectionKey, CrawlType::Photosets, $nsid);
        $this->spider->enqueueTarget($run, TaskType::PhotosetsList, $nsid, null, 1);
        $this->spider->dispatchDueTargets();

        return $run;
    }

    public function startGalleries(string $connectionKey, string $tokenPayload, string $nsid, ?string $appProfile = null): CrawlRun
    {
        $this->ensureConnection($connectionKey, $tokenPayload, $appProfile);
        $this->guardGlobalPause();

        $run = $this->spider->createRun($connectionKey, CrawlType::Galleries, $nsid);
        $this->spider->enqueueTarget($run, TaskType::GalleriesList, $nsid, null, 1);
        $this->spider->dispatchDueTargets();

        return $run;
    }

    public function startFavorites(string $connectionKey, string $tokenPayload, string $nsid, ?string $appProfile = null): CrawlRun
    {
        $this->ensureConnection($connectionKey, $tokenPayload, $appProfile);
        $this->guardGlobalPause();

        $run = $this->spider->createRun($connectionKey, CrawlType::Favorites, $nsid);
        $this->spider->enqueueTarget($run, TaskType::FavoritesPage, $nsid, null, 1);
        $this->spider->dispatchDueTargets();

        return $run;
    }

    /**
     * @return list<CrawlTaskSpec>
     */
    public function initialContactsSpecs(): array
    {
        return [new CrawlTaskSpec(TaskType::ContactsPage, page: 1)];
    }

    private function ensureConnection(string $connectionKey, string $tokenPayload, ?string $appProfile = null): Connection
    {
        $profile = $appProfile !== null
            ? XFlickrConfig::sanitizeProfileSlug($appProfile)
            : XFlickrConfig::defaultAppProfile();

        return Connection::query()->updateOrCreate(
            ['connection_key' => $connectionKey],
            [
                'app_profile' => $profile,
                'token_payload' => $tokenPayload,
            ],
        );
    }

    private function guardGlobalPause(): void
    {
        if (XFlickrConfig::globalPause()) {
            throw new RuntimeException('Global crawl pause is active.');
        }
    }
}
