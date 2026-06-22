<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use JOOservices\XFlickrCrawler\DTO\FlickrPermit;
use JOOservices\XFlickrCrawler\Support\XFlickrConfig;

final class FlickrRequestLimiter
{
    public function acquire(string $connectionKey): FlickrPermit
    {
        if (XFlickrConfig::globalPause()) {
            return new FlickrPermit(false, 60);
        }

        $pauseUntil = $this->globalPauseUntil($connectionKey);
        if ($pauseUntil !== null && $pauseUntil->isFuture()) {
            return new FlickrPermit(false, max(1, $pauseUntil->diffInSeconds(now())));
        }

        $hourly = $this->checkHourlyWindow($connectionKey);
        if (! $hourly['acquired']) {
            return new FlickrPermit(false, max(1, (int) ceil($hourly['retry_after_ms'] / 1000)));
        }

        $gap = $this->claimMinGap($connectionKey);
        if (! $gap['acquired']) {
            return new FlickrPermit(false, max(1, (int) ceil($gap['retry_after_ms'] / 1000)));
        }

        $this->recordRequest($connectionKey);

        return new FlickrPermit(true, 0, CarbonImmutable::now());
    }

    public function triggerGlobalCooldown(string $connectionKey): CarbonImmutable
    {
        $seconds = XFlickrConfig::throttle('rate_limit_backoff_seconds', 3600);
        $until = CarbonImmutable::now()->addSeconds($seconds);
        Redis::set($this->pauseKey($connectionKey), (string) $until->getTimestamp());

        return $until;
    }

    /**
     * @return array<string, mixed>
     */
    public function state(string $connectionKey): array
    {
        $windowSeconds = XFlickrConfig::throttle('window_seconds', 3600);
        $maxRequests = XFlickrConfig::maxRequestsPerHour();
        $nowMs = (int) floor(microtime(true) * 1000);
        $windowStart = $nowMs - ($windowSeconds * 1000);

        Redis::zremrangebyscore($this->windowKey($connectionKey), '0', (string) $windowStart);
        $used = (int) Redis::zcard($this->windowKey($connectionKey));
        $pauseUntil = $this->globalPauseUntil($connectionKey);

        return [
            'connection_key' => $connectionKey,
            'max_requests_per_hour' => $maxRequests,
            'requests_used' => $used,
            'requests_remaining' => max(0, $maxRequests - $used),
            'min_gap_ms' => XFlickrConfig::throttle('min_gap_ms', 333),
            'global_pause' => XFlickrConfig::globalPause(),
            'global_pause_until' => $pauseUntil?->toISOString(),
            'global_pause_seconds_remaining' => $pauseUntil?->isFuture()
                ? max(0, $pauseUntil->diffInSeconds(now()))
                : 0,
        ];
    }

    private function pauseKey(string $connectionKey): string
    {
        return "xflickr:pause:{$connectionKey}";
    }

    private function windowKey(string $connectionKey): string
    {
        return "xflickr:req:{$connectionKey}:window";
    }

    private function lastRequestKey(string $connectionKey): string
    {
        return "xflickr:req:{$connectionKey}:last";
    }

    private function globalPauseUntil(string $connectionKey): ?CarbonImmutable
    {
        $value = Redis::get($this->pauseKey($connectionKey));
        if (! is_numeric($value)) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp((int) $value);
    }

    /**
     * @return array{acquired: bool, retry_after_ms: int}
     */
    private function checkHourlyWindow(string $connectionKey): array
    {
        $windowSeconds = XFlickrConfig::throttle('window_seconds', 3600);
        $maxRequests = XFlickrConfig::maxRequestsPerHour();
        $nowMs = (int) floor(microtime(true) * 1000);
        $windowStart = $nowMs - ($windowSeconds * 1000);
        $windowKey = $this->windowKey($connectionKey);

        Redis::zremrangebyscore($windowKey, '0', (string) $windowStart);
        $count = (int) Redis::zcard($windowKey);

        if ($count >= $maxRequests) {
            $oldest = Redis::zrange($windowKey, 0, 0, ['WITHSCORES' => true]);
            $oldestScore = 0;
            if (is_array($oldest)) {
                foreach ($oldest as $score) {
                    if (is_numeric($score)) {
                        $oldestScore = (int) $score;
                        break;
                    }
                }
            }

            $retryAfterMs = max(0, ($oldestScore + ($windowSeconds * 1000)) - $nowMs);

            return ['acquired' => false, 'retry_after_ms' => $retryAfterMs];
        }

        return ['acquired' => true, 'retry_after_ms' => 0];
    }

    /**
     * @return array{acquired: bool, retry_after_ms: int}
     */
    private function claimMinGap(string $connectionKey): array
    {
        $gapMs = XFlickrConfig::throttle('min_gap_ms', 333);
        $gapSeconds = max(0.0, $gapMs / 1000);
        $now = microtime(true);

        $result = Redis::connection()->command('eval', [
            $this->claimScript(),
            [
                $this->lastRequestKey($connectionKey),
                (string) $now,
                (string) $gapSeconds,
                '3600',
            ],
            1,
        ]);

        if (! is_array($result)) {
            return ['acquired' => true, 'retry_after_ms' => 0];
        }

        $acquired = $result[0] ?? 1;
        $retryAfterMs = $result[1] ?? 0;

        return [
            'acquired' => (is_numeric($acquired) ? (int) $acquired : 1) === 1,
            'retry_after_ms' => max(0, is_numeric($retryAfterMs) ? (int) $retryAfterMs : 0),
        ];
    }

    private function recordRequest(string $connectionKey): void
    {
        $nowMs = (int) floor(microtime(true) * 1000);
        $windowSeconds = XFlickrConfig::throttle('window_seconds', 3600);
        $windowKey = $this->windowKey($connectionKey);

        $member = Str::uuid()->toString();
        Redis::zadd($windowKey, $nowMs, $member);
        Redis::expire($windowKey, $windowSeconds + 60);
    }

    private function claimScript(): string
    {
        return <<<'LUA'
local now = tonumber(ARGV[1])
local gap = tonumber(ARGV[2])
local ttl = tonumber(ARGV[3])

if gap <= 0 then
    redis.call('SETEX', KEYS[1], ttl, tostring(now))
    return {1, 0}
end

local last = tonumber(redis.call('GET', KEYS[1]) or '')
local availableAt = now

if last ~= nil then
    availableAt = math.max(availableAt, last + gap)
end

if availableAt <= now then
    redis.call('SETEX', KEYS[1], ttl, tostring(now))
    return {1, 0}
end

return {0, math.ceil((availableAt - now) * 1000)}
LUA;
    }
}
