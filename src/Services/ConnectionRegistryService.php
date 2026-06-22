<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use Illuminate\Database\Eloquent\Collection;
use JOOservices\XFlickrCrawler\Models\Connection;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

final class ConnectionRegistryService
{
    /**
     * @param  array<string, mixed>|string  $tokenPayload
     */
    public function register(
        string $connectionKey,
        array|string $tokenPayload,
        ?string $appProfile = null,
        ?string $username = null,
        ?string $fullname = null,
        bool $activate = true,
    ): Connection {
        $profile = $appProfile !== null
            ? XFlickrConfig::sanitizeProfileSlug($appProfile)
            : XFlickrConfig::defaultAppProfile();

        $payload = is_array($tokenPayload)
            ? json_encode($tokenPayload, JSON_THROW_ON_ERROR)
            : $tokenPayload;

        if ($activate) {
            Connection::query()->update(['is_active' => false]);
        }

        $connection = Connection::query()->updateOrCreate(
            ['connection_key' => $connectionKey],
            [
                'app_profile' => $profile,
                'token_payload' => $payload,
                'username' => $username,
                'fullname' => $fullname,
                'connected_at' => now(),
                'disconnected_at' => null,
                'is_active' => $activate,
            ],
        );

        return $connection->fresh() ?? $connection;
    }

    public function disconnect(string $connectionKey): void
    {
        Connection::query()
            ->where('connection_key', $connectionKey)
            ->update([
                'token_payload' => '',
                'is_active' => false,
                'disconnected_at' => now(),
            ]);
    }

    public function activate(string $connectionKey): void
    {
        Connection::query()->update(['is_active' => false]);

        Connection::query()
            ->where('connection_key', $connectionKey)
            ->update([
                'is_active' => true,
                'disconnected_at' => null,
            ]);
    }

    public function active(): ?Connection
    {
        return Connection::query()
            ->where('is_active', true)
            ->where('disconnected_at', null)
            ->where('token_payload', '!=', '')
            ->latest('connected_at')
            ->first();
    }

    /**
     * @return Collection<int, Connection>
     */
    public function list(): Collection
    {
        return Connection::query()
            ->orderByDesc('connected_at')
            ->get();
    }

    public function find(string $connectionKey): ?Connection
    {
        return Connection::query()
            ->where('connection_key', $connectionKey)
            ->first();
    }
}
