<?php

namespace App\Contracts;

interface DashboardDataProviderInterface
{
    /**
     * Fetch raw dashboard JSON from the source (e.g. Gemini API or file).
     *
     * @param array<string, mixed> $context Facts the source must build on: 'market' (real prices,
     *                                       indicator key => value), 'baseline_countries' (last week's
     *                                       average scores), 'baseline_chaos' (previous chaos range), 'headlines' (real news of the last day)
     * @return array<string, mixed> Decoded JSON as associative array
     * @throws \RuntimeException On fetch/parse failure
     */
    public function fetch(string $when = 'now', array $context = []): array;
}
