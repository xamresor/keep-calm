<?php

namespace App\Services;

use App\Contracts\DashboardParserInterface;

class DashboardParser implements DashboardParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(array $raw): array
    {
        $lastUpdated = $this->normalizeLastUpdated($raw['last_updated'] ?? null);

        $chaos = $raw['global_chaos_probability_100_percent'] ?? null;
        $newsTitles = $chaos['last_updated_news_titles'] ?? [];
        $indicators = $raw['key_indicators_today'] ?? [];
        $shippingChokepoint = $indicators['most_critical_shipping_chokepoint_status'] ?? null;

        $indicatorData = [
            'gold_price' => $indicators['gold_price'] ?? null,
            'brent_crude' => $indicators['brent_crude'] ?? null,
            'vix_fear_index' => $indicators['vix_fear_index'] ?? null,
            'global_economic_policy_uncertainty_index' => $indicators['global_economic_policy_uncertainty_index'] ?? null,
        ];

        $snapshot = [
            'last_updated' => $lastUpdated,
            'global_chaos' => $chaos,
            'key_indicators' => $indicatorData,
            'shipping_chokepoint' => $shippingChokepoint,
            'last_updated_news_titles' => $newsTitles,
        ];

        $countries = [];
        foreach ($raw['countries'] ?? [] as $c) {
            $countries[] = [
                'name' => $c['name'] ?? '',
                'liquidity' => (int) ($c['liquidity'] ?? 0),
                'logistics' => (int) ($c['logistics'] ?? 0),
                'legitimacy' => (int) ($c['legitimacy'] ?? 0),
                'overall' => (float) ($c['overall'] ?? 0),
                'family_safety_note' => $c['family_safety_note'] ?? [],
            ];
        }

        $scenarios = [];
        foreach ($raw['scenarios'] ?? [] as $s) {
            $scenarios[] = [
                'name' => $s['name'] ?? '',
                'description' => $s['description'] ?? [],
                'when_visible' => $s['when_visible'] ?? null,
                'earliest_date' => $s['earliest_date'] ?? null,
                'probability_percent' => isset($s['probability_percent']) ? (int) $s['probability_percent'] : null,
            ];
        }

        return [
            'snapshot' => $snapshot,
            'countries' => $countries,
            'scenarios' => $scenarios,
        ];
    }

    private function normalizeLastUpdated(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            try {
                return (new \DateTimeImmutable($value))->format('Y-m-d');
            } catch (\Throwable) {
                // Fall back to server date when upstream date is malformed.
            }
        }

        return (new \DateTimeImmutable('now'))->format('Y-m-d');
    }
}
