<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use JOOservices\Flickr\Auth\InMemoryTokenStore;
use JOOservices\Flickr\Config\FlickrConfig;
use JOOservices\Flickr\Contracts\Client\FlickrTransportContract;
use JOOservices\Flickr\DTO\Auth\AccessTokenData;
use JOOservices\Flickr\Flickr;
use JOOservices\Flickr\FlickrFactory;
use JOOservices\XFlickrCrawler\DTO\FlickrAppCredentialsDto;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;
use RuntimeException;

final class FlickrClientFactory
{
    public function __construct(
        private readonly ?FlickrTransportContract $transport = null,
    ) {}

    public function forConnection(string $connectionKey): Flickr
    {
        $connection = Connection::query()
            ->where('connection_key', $connectionKey)
            ->first();

        if ($connection === null) {
            throw new RuntimeException("Flickr connection [{$connectionKey}] was not found.");
        }

        $credentials = XFlickrConfig::appCredentials($connection->app_profile);

        return $this->makeClient($credentials, $connection->token_payload);
    }

    public function makeClient(FlickrAppCredentialsDto $credentials, string $tokenPayload): Flickr
    {
        $token = $this->parseTokenPayload($tokenPayload);
        $config = FlickrConfig::from([
            'apiKey' => $credentials->apiKey,
            'apiSecret' => $credentials->apiSecret,
        ]);

        return FlickrFactory::make(
            $config,
            tokenStore: new InMemoryTokenStore($token),
            transport: $this->transport,
        );
    }

    private function parseTokenPayload(string $tokenPayload): AccessTokenData
    {
        $decoded = json_decode($tokenPayload, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid Flickr token payload JSON.');
        }

        $oauthToken = (string) ($decoded['oauthToken'] ?? $decoded['oauth_token'] ?? '');
        $oauthTokenSecret = (string) ($decoded['oauthTokenSecret'] ?? $decoded['oauth_token_secret'] ?? '');

        if ($oauthToken === '' || $oauthTokenSecret === '') {
            throw new RuntimeException('Flickr token payload must include oauthToken and oauthTokenSecret.');
        }

        return AccessTokenData::from([
            'oauthToken' => $oauthToken,
            'oauthTokenSecret' => $oauthTokenSecret,
            'userNsid' => $decoded['userNsid'] ?? $decoded['user_nsid'] ?? null,
            'username' => $decoded['username'] ?? null,
            'fullname' => $decoded['fullname'] ?? null,
        ]);
    }
}
