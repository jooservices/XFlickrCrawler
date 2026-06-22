<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Repositories;

use Illuminate\Support\Facades\DB;
use JOOservices\XFlickrCrawler\Models\Contact;

final class ContactRepository
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

        $table = (new Contact)->getTable();
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
                ['nsid'],
                ['username', 'realname', 'friend', 'family', 'raw_payload', 'updated_at'],
            );
            $total += count($batch);
        }

        return $total;
    }
}
