<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Support;

final class PeoplePhotosParams
{
    /**
     * @return array<string, mixed>
     */
    public static function query(string $userId, int $page, int $perPage): array
    {
        return [
            'user_id' => $userId,
            'page' => $page,
            'per_page' => $perPage,
            'safe_search' => XFlickrConfig::crawlInt('people_photos_safe_search', 1),
            'extras' => 'owner_name,path_alias,url_sq,url_t,url_s,url_m,url_o',
        ];
    }
}
