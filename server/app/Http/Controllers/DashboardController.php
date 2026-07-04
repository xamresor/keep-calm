<?php

namespace App\Http\Controllers;

use App\Contracts\DashboardCacheInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Models\Snapshot;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    public function __construct(
        private readonly DashboardCacheInterface $cache,
        private readonly DashboardRepositoryInterface $repository,
    ) {
    }

    /**
     * GET /api/dashboard - Returns cached dashboard or latest from DB.
     */
    public function index(): JsonResponse
    {
        $data = $this->cache->get();

        if ($data === null) {
            $snapshot = $this->repository->getLatest();

            if ($snapshot === null) {
                return response()->json([
                    'error' => 'No dashboard data available. Run php artisan dashboard:ingest first.',
                ], 404);
            }

            $data = $this->snapshotToApiShape($snapshot);
            $this->cache->put($data);
        }

        return response()->json($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotToApiShape(Snapshot $snapshot): array
    {
        $chaos = $snapshot->global_chaos ?? [];

        $countries = $snapshot->countries->map(fn ($c) => [
            'name' => $c->name,
            'liquidity' => $c->liquidity,
            'logistics' => $c->logistics,
            'legitimacy' => $c->legitimacy,
            'overall' => $c->overall,
            'family_safety_note' => $c->family_safety_note ?? [],
        ])->toArray();

        $scenarios = $snapshot->scenarios->map(fn ($s) => [
            'name' => $s->name,
            'description' => $s->description ?? [],
            'when_visible' => $s->when_visible,
            'earliest_date' => $s->earliest_date,
            'probability_percent' => $s->probability_percent,
        ])->toArray();

        $indicators = $snapshot->key_indicators ?? [];
        $indicators['most_critical_shipping_chokepoint_status'] = $snapshot->shipping_chokepoint;

        return [
            'last_updated' => $snapshot->last_updated->format('Y-m-d'),
            'global_chaos_probability_100_percent' => $chaos,
            'last_updated_news_titles' => $snapshot->last_updated_news_titles ?? [],
            'countries' => $countries,
            'scenarios' => $scenarios,
            'key_indicators_today' => $indicators,
        ];
    }
}
