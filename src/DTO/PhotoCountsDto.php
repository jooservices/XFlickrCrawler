<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\DTO;

final readonly class PhotoCountsDto
{
    public function __construct(
        public int $photos,
        public int $photosets,
        public int $galleries,
        public int $favorites,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'photos' => $this->photos,
            'photosets' => $this->photosets,
            'galleries' => $this->galleries,
            'favorites' => $this->favorites,
        ];
    }
}
