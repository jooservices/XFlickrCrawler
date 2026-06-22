<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\Flickr\Exceptions\TransportException;
use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use Throwable;

final class FlickrApiOutcomeClassifier
{
    public function fromApiResponse(ApiResponseData $response): ApiOutcome
    {
        if ($response->ok) {
            return ApiOutcome::Success;
        }

        $code = $response->error?->code;
        $message = strtolower($response->error !== null ? $response->error->message : '');

        if ($code === 99 || str_contains($message, 'limit') || str_contains($message, 'rate')) {
            return ApiOutcome::RateLimited;
        }

        return ApiOutcome::ApiError;
    }

    public function fromThrowable(Throwable $throwable): ApiOutcome
    {
        if ($throwable instanceof TransportException) {
            $message = strtolower($throwable->getMessage());
            if (str_contains($message, '429') || str_contains($message, 'too many requests')) {
                return ApiOutcome::RateLimited;
            }

            return ApiOutcome::TransportError;
        }

        return ApiOutcome::TransportError;
    }
}
