<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use JOOservices\XFlickrCrawler\DTO\PhotoCountsDto;
use JOOservices\XFlickrCrawler\Models\ConnectionContact;
use JOOservices\XFlickrCrawler\Models\Contact;
use JOOservices\XFlickrCrawler\Models\Favorite;
use JOOservices\XFlickrCrawler\Models\Gallery;
use JOOservices\XFlickrCrawler\Models\Photo;
use JOOservices\XFlickrCrawler\Models\Photoset;

final class CrawlerCatalog
{
    public function countsForSubject(string $connectionKey, string $subjectNsid): PhotoCountsDto
    {
        return new PhotoCountsDto(
            photos: (int) Photo::query()->where('owner_nsid', $subjectNsid)->count(),
            photosets: (int) Photoset::query()->where('owner_nsid', $subjectNsid)->count(),
            galleries: (int) Gallery::query()->where('owner_nsid', $subjectNsid)->count(),
            favorites: (int) Favorite::query()
                ->where('connection_key', $connectionKey)
                ->where('subject_nsid', $subjectNsid)
                ->count(),
        );
    }

    /**
     * @return Collection<int, ConnectionContact>
     */
    public function contactsForConnection(string $connectionKey): Collection
    {
        return ConnectionContact::query()
            ->where('connection_key', $connectionKey)
            ->orderBy('discovered_at')
            ->get();
    }

    /**
     * @return Collection<int, Contact>
     */
    public function contactProfilesForConnection(string $connectionKey): Collection
    {
        $nsids = $this->contactsForConnection($connectionKey)
            ->pluck('contact_nsid')
            ->all();

        if ($nsids === []) {
            return new Collection;
        }

        return Contact::query()
            ->whereIn('nsid', $nsids)
            ->orderBy('username')
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function contactProfilesForConnectionPaginated(
        string $connectionKey,
        ?string $search,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $contactsTable = (new Contact)->getTable();
        $connectionContactsTable = (new ConnectionContact)->getTable();

        $query = Contact::query()
            ->select("{$contactsTable}.*")
            ->join(
                $connectionContactsTable,
                "{$connectionContactsTable}.contact_nsid",
                '=',
                "{$contactsTable}.nsid",
            )
            ->where("{$connectionContactsTable}.connection_key", $connectionKey);

        if ($search !== null && $search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($contactsTable, $like): void {
                $builder
                    ->where("{$contactsTable}.username", 'like', $like)
                    ->orWhere("{$contactsTable}.realname", 'like', $like)
                    ->orWhere("{$contactsTable}.nsid", 'like', $like);
            });
        }

        return $query
            ->orderBy("{$contactsTable}.username")
            ->paginate($perPage, ["{$contactsTable}.*"], 'page', $page);
    }
}
