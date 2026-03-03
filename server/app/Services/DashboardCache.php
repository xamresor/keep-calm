<?php

namespace App\Services;

use App\Contracts\DashboardCacheInterface;
use Illuminate\Support\Facades\Cache;

class DashboardCache implements DashboardCacheInterface
{
    private const CACHE_KEY = 'dashboard:latest';

    private const TTL_SECONDS = 3600; // 1 hour

    /**
     * @inheritDoc
     */
    public function get(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);

        return is_array($cached) ? $cached : null;
    }

    /**
     * @inheritDoc
     */
    public function put(array $data): void
    {
        Cache::put(self::CACHE_KEY, $data, self::TTL_SECONDS);
    }

    /**
     * @inheritDoc
     */
    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
