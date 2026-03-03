<?php

namespace App\Services;

use App\Contracts\DashboardDataProviderInterface;
use RuntimeException;

class FileDataProvider implements DashboardDataProviderInterface
{
    public function __construct(
        private readonly string $path,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fetch(string $when = 'now'): array
    {
        if (!is_readable($this->path)) {
            throw new RuntimeException("Dashboard file not readable: {$this->path}");
        }

        $content = file_get_contents($this->path);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON in dashboard file: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
