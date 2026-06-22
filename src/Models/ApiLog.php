<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Models;

use Illuminate\Database\Eloquent\Model;
use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

final class ApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'connection_key',
        'xflickr_crawl_run_id',
        'xflickr_crawl_target_id',
        'api_method',
        'outcome',
        'latency_ms',
        'error_code',
        'error_message',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'outcome' => ApiOutcome::class,
            'latency_ms' => 'integer',
            'error_code' => 'integer',
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return XFlickrConfig::table('api_logs');
    }
}
