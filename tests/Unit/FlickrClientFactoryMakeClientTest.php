<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Unit;

use JOOservices\Flickr\Flickr;
use JOOservices\XFlickrCrawler\DTO\FlickrAppCredentialsDto;
use JOOservices\XFlickrCrawler\Services\FlickrClientFactory;
use JOOservices\XFlickrCrawler\Tests\TestCase;
use RuntimeException;

final class FlickrClientFactoryMakeClientTest extends TestCase
{
    public function test_make_client_with_valid_token(): void
    {
        $factory = new FlickrClientFactory;
        $client = $factory->makeClient(
            new FlickrAppCredentialsDto(apiKey: 'k', apiSecret: 's'),
            $this->sampleToken(),
        );

        $this->assertInstanceOf(Flickr::class, $client);
    }

    public function test_make_client_rejects_invalid_json(): void
    {
        $factory = new FlickrClientFactory;

        $this->expectException(RuntimeException::class);
        $factory->makeClient(
            new FlickrAppCredentialsDto(apiKey: 'k', apiSecret: 's'),
            'not-json',
        );
    }

    public function test_for_connection_throws_when_row_missing(): void
    {
        $factory = new FlickrClientFactory;

        $this->expectException(RuntimeException::class);
        $factory->forConnection('missing-connection');
    }
}
