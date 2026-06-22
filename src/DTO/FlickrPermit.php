<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\DTO;

use Carbon\CarbonImmutable;

final class FlickrPermit
{
    public function __construct(
        public readonly bool $acquired,
        public readonly int $retryAfterSeconds,
        public readonly ?CarbonImmutable $acquiredAt = null,
    ) {}
}
