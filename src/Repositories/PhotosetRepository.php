<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Repositories;

use Illuminate\Support\Facades\DB;
use JOOservices\XFlickrCrawler\Models\Photoset;

final class PhotosetRepository
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
        $table = (new Photoset)->getTable();
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
                ['flickr_photoset_id'],
                ['owner_nsid', 'title', 'description', 'photo_count', 'raw_payload', 'updated_at'],
            );
            $total += count($batch);
        }

        return $total;
    }

    /**
     * @return array<string, int> flickr_photoset_id => internal id
     */
    public function idsByFlickrPhotosetIds(array $flickrPhotosetIds): array
    {
        if ($flickrPhotosetIds === []) {
            return [];
        }

        return Photoset::query()
            ->whereIn('flickr_photoset_id', $flickrPhotosetIds)
            ->pluck('id', 'flickr_photoset_id')
            ->all();
    }
}
