<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Models;

use Illuminate\Database\Eloquent\Model;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

/**
 * @property string $connection_key
 * @property string $app_profile
 * @property string $token_payload
 */
final class Connection extends Model
{
    protected $fillable = [
        'connection_key',
        'app_profile',
        'token_payload',
        'label',
        'last_crawled_at',
    ];

    protected function casts(): array
    {
        return [
            'last_crawled_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return XFlickrConfig::table('connections');
    }
}
