<?php

namespace App\Services;

use App\Contracts\DashboardCacheInterface;
use App\Contracts\DashboardDataProviderInterface;
use App\Contracts\DashboardParserInterface;
use App\Contracts\IngestServiceInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Models\Snapshot;

class IngestService implements IngestServiceInterface
{
    public function __construct(
        private readonly DashboardDataProviderInterface $provider,
        private readonly DashboardParserInterface $parser,
        private readonly DashboardRepositoryInterface $repository,
        private readonly DashboardCacheInterface $cache,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function run(string $when = 'now'): array
    {
        try {
            $targetDate = $this->resolveTargetDate($when);
            $raw = $this->provider->fetch($when);
            $parsed = $this->parser->parse($raw);
            $parsed['snapshot']['last_updated'] = $targetDate;
            $snapshot = $this->repository->store($parsed);
            $forCache = $this->snapshotToApiShape($snapshot);
            $this->cache->put($forCache);

            return [
                'success' => true,
                'last_updated' => $targetDate,
                'message' => 'Ingest completed successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'last_updated' => null,
                'message' => 'Ingest failed: ' . $e->getMessage(),
            ];
        }
    }

    private function resolveTargetDate(string $when): string
    {
        try {
            return (new \DateTimeImmutable($when))->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Invalid date expression '{$when}'. Use values like 'now', '-1 day', '-10 days'.", 0, $e);
        }
    }

    /**
     * Convert Snapshot model to frontend JSON shape.
     *
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
