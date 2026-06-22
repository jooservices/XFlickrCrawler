<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use JOOservices\XFlickrCrawler\DTO\FlickrPermit;

final class FlickrPermitAcquirer
{
    public function acquire(FlickrRequestLimiter $limiter, string $connectionKey, int $maxAttempts = 120): FlickrPermit
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $permit = $limiter->acquire($connectionKey);
            if ($permit->acquired) {
                return $permit;
            }

            sleep(min(30, max(1, $permit->retryAfterSeconds)));
        }

        return new FlickrPermit(false, 60);
    }
}
