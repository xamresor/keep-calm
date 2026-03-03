<?php

namespace App\Contracts;

use App\Models\Snapshot;

interface DashboardRepositoryInterface
{
    /**
     * Store parsed dashboard data. Upserts by last_updated.
     *
     * @param array{snapshot: array, countries: array, scenarios: array} $data
     */
    public function store(array $data): Snapshot;

    /**
     * Get the latest snapshot with relationships, or null if none.
     */
    public function getLatest(): ?Snapshot;
}
