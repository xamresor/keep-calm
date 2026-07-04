<?php

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;
use App\Models\Country;
use App\Models\Scenario;
use App\Models\Snapshot;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function store(array $data): Snapshot
    {
        return DB::transaction(function () use ($data) {
            $snapshotData = $data['snapshot'];

            $snapshot = Snapshot::query()
                ->whereDate('last_updated', $snapshotData['last_updated'])
                ->first();

            if ($snapshot) {
                $snapshot->fill([
                    'last_updated' => $snapshotData['last_updated'],
                    'global_chaos' => $snapshotData['global_chaos'],
                    'key_indicators' => $snapshotData['key_indicators'],
                    'shipping_chokepoint' => $snapshotData['shipping_chokepoint'],
                    'last_updated_news_titles' => $snapshotData['last_updated_news_titles'] ?? [],
                ])->save();
            } else {
                $snapshot = Snapshot::create([
                    'last_updated' => $snapshotData['last_updated'],
                    'global_chaos' => $snapshotData['global_chaos'],
                    'key_indicators' => $snapshotData['key_indicators'],
                    'shipping_chokepoint' => $snapshotData['shipping_chokepoint'],
                    'last_updated_news_titles' => $snapshotData['last_updated_news_titles'] ?? [],
                ]);
            }

            // Defensive cleanup for pre-existing duplicate rows on same date.
            Snapshot::where('last_updated', $snapshotData['last_updated'])
                ->where('id', '!=', $snapshot->id)
                ->delete();

            $this->syncCountries($snapshot, $data['countries']);
            $this->syncScenarios($snapshot, $data['scenarios']);

            return $snapshot->fresh(['countries', 'scenarios']);
        });
    }

    /**
     * @inheritDoc
     */
    public function getLatest(): ?Snapshot
    {
        return Snapshot::with(['countries', 'scenarios'])
            ->orderByDesc('last_updated')
            ->first();
    }

    /**
     * @param array<int, array> $countries
     */
    private function syncCountries(Snapshot $snapshot, array $countries): void
    {
        $snapshot->countries()->delete();

        foreach ($countries as $c) {
            Country::create(array_merge($c, ['snapshot_id' => $snapshot->id]));
        }
    }

    /**
     * @param array<int, array> $scenarios
     */
    private function syncScenarios(Snapshot $snapshot, array $scenarios): void
    {
        $snapshot->scenarios()->delete();

        foreach ($scenarios as $s) {
            Scenario::create(array_merge($s, ['snapshot_id' => $snapshot->id]));
        }
    }
}
