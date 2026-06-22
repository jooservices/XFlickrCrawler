<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Repositories;

use Illuminate\Support\Facades\DB;
use JOOservices\XFlickrCrawler\Models\Photo;

final class PhotoRepository
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function upsertMany(array $rows, ?int $chunk = null): int
    {
        if ($rows === []) {
            return 0;
        }

        $chunkSize = $chunk ?? (int) config('xflickr-crawler.bulk.chunk_size', 250);
        $table = (new Photo)->getTable();
        $now = now();
        $total = 0;

        foreach (array_chunk($rows, $chunkSize) as $batch) {
            $payload = [];
            foreach ($batch as $row) {
                $payload[] = array_merge($row, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
            }

            DB::table($table)->upsert(
                $payload,
                ['flickr_photo_id'],
                ['owner_nsid', 'title', 'secret', 'server', 'farm', 'raw_payload', 'updated_at'],
            );
            $total += count($batch);
        }

        return $total;
    }

    /**
     * @return array<string, int> flickr_photo_id => internal id
     */
    public function idsByFlickrPhotoIds(array $flickrPhotoIds): array
    {
        if ($flickrPhotoIds === []) {
            return [];
        }

        return Photo::query()
            ->whereIn('flickr_photo_id', $flickrPhotoIds)
            ->pluck('id', 'flickr_photo_id')
            ->all();
    }
}
