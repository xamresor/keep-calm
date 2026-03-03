<?php

namespace App\Contracts;

interface DashboardCacheInterface
{
    /**
     * Get cached dashboard JSON.
     *
     * @return array<string, mixed>|null
     */
    public function get(): ?array;

    /**
     * Put dashboard JSON into cache.
     *
     * @param array<string, mixed> $data
     */
    public function put(array $data): void;

    /**
     * Invalidate/clear the cache.
     */
    public function forget(): void;
}
