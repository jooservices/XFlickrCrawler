<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Repositories;

use Illuminate\Support\Facades\DB;
use JOOservices\XFlickrCrawler\Models\Favorite;

final class FavoriteRepository
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
        $table = (new Favorite)->getTable();
        $total = 0;

        foreach (array_chunk($rows, $chunkSize) as $batch) {
            DB::table($table)->upsert(
                $batch,
                ['connection_key', 'subject_nsid', 'xflickr_photo_id'],
                ['photo_owner_nsid', 'discovered_at'],
            );
            $total += count($batch);
        }

        return $total;
    }
}
