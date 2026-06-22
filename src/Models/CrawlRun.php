<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JOOservices\XFlickrCrawler\Enums\CrawlRunStatus;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

/**
 * @property int $id
 * @property string $connection_key
 * @property CrawlRunStatus $status
 */
final class CrawlRun extends Model
{
    protected $fillable = [
        'connection_key',
        'crawl_type',
        'subject_nsid',
        'status',
        'contacts_discovered',
        'photos_discovered',
        'api_calls',
        'started_at',
        'completed_at',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => CrawlRunStatus::class,
            'contacts_discovered' => 'integer',
            'photos_discovered' => 'integer',
            'api_calls' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return XFlickrConfig::table('crawl_runs');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(CrawlTarget::class, 'xflickr_crawl_run_id');
    }
}
