<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Repositories;

use Illuminate\Support\Facades\DB;
use JOOservices\XFlickrCrawler\Models\ConnectionContact;

final class ConnectionContactRepository
{
    /**
     * @param  list<string>  $contactNsids
     */
    public function upsertMany(string $connectionKey, array $contactNsids, ?int $chunk = null): int
    {
        if ($contactNsids === []) {
            return 0;
        }

        $chunkSize = $chunk ?? (int) config('xflickr-crawler.bulk.chunk_size', 250);
        $table = (new ConnectionContact)->getTable();
        $now = now()->toDateTimeString();
        $total = 0;

        foreach (array_chunk(array_unique($contactNsids), $chunkSize) as $batch) {
            $payload = [];
            foreach ($batch as $nsid) {
                if ($nsid === '') {
                    continue;
                }

                $payload[] = [
                    'connection_key' => $connectionKey,
                    'contact_nsid' => $nsid,
                    'discovered_at' => $now,
                ];
            }

            if ($payload === []) {
                continue;
            }

            DB::table($table)->upsert(
                $payload,
                ['connection_key', 'contact_nsid'],
                ['discovered_at'],
            );
            $total += count($payload);
        }

        return $total;
    }
}
