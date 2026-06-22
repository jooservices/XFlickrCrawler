<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\Flickr\DTO\Common\ApiErrorData;
use JOOservices\Flickr\DTO\Common\ApiResponseData;
use JOOservices\Flickr\Exceptions\TransportException;
use JOOservices\XFlickrCrawler\Enums\ApiOutcome;
use JOOservices\XFlickrCrawler\Services\FlickrApiOutcomeClassifier;
use JOOservices\XFlickrCrawler\Tests\TestCase;

final class FlickrApiOutcomeClassifierTest extends TestCase
{
    private FlickrApiOutcomeClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new FlickrApiOutcomeClassifier;
    }

    public function test_success_response(): void
    {
        $outcome = $this->classifier->fromApiResponse(new ApiResponseData(ok: true, data: []));

        $this->assertSame(ApiOutcome::Success, $outcome);
    }

    public function test_rate_limited_by_code(): void
    {
        $response = new ApiResponseData(
            ok: false,
            data: [],
            error: new ApiErrorData(code: 99, message: 'Limit reached'),
        );

        $this->assertSame(ApiOutcome::RateLimited, $this->classifier->fromApiResponse($response));
    }

    public function test_api_error(): void
    {
        $response = new ApiResponseData(
            ok: false,
            data: [],
            error: new ApiErrorData(code: 1, message: 'Invalid signature'),
        );

        $this->assertSame(ApiOutcome::ApiError, $this->classifier->fromApiResponse($response));
    }

    public function test_transport_rate_limit(): void
    {
        $outcome = $this->classifier->fromThrowable(new TransportException('HTTP 429 Too Many Requests'));

        $this->assertSame(ApiOutcome::RateLimited, $outcome);
    }

    public function test_transport_error(): void
    {
        $outcome = $this->classifier->fromThrowable(new TransportException('Connection reset'));

        $this->assertSame(ApiOutcome::TransportError, $outcome);
    }
}
