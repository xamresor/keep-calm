<?php

namespace App\Contracts;

interface DashboardParserInterface
{
    /**
     * Parse raw dashboard JSON into normalized structure for persistence.
     *
     * @param array<string, mixed> $raw
     * @return array{snapshot: array, countries: array, scenarios: array}
     */
    public function parse(array $raw): array;
}
