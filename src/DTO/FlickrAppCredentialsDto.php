<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\DTO;

final class FlickrAppCredentialsDto
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $apiSecret,
        public readonly ?string $label = null,
    ) {}
}
