<?php

declare(strict_types=1);

namespace JOOservices\XFlickrCrawler\Tests\Support;

final class InMemoryConfigStore
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function get(string $path, mixed $default = null): mixed
    {
        return $this->items[$path] ?? $default;
    }

    public function has(string $path): bool
    {
        return array_key_exists($path, $this->items);
    }

    public function set(string $path, mixed $value, ?string $type = null): void
    {
        $this->items[$path] = $value;
    }
}
