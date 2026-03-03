<?php

namespace App\Contracts;

interface DashboardDataProviderInterface
{
    /**
     * Fetch raw dashboard JSON from the source (e.g. Gemini API or file).
     *
     * @return array<string, mixed> Decoded JSON as associative array
     * @throws \RuntimeException On fetch/parse failure
     */
    public function fetch(string $when = 'now'): array;
}
