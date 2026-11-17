<?php

namespace App\Services;

use App\Models\Snapshot;
use App\Services\Prices\PriceFeed;
use Illuminate\Support\Facades\DB;

/**
 * Turns a stored snapshot into the JSON the dashboard page reads, and adds the
 * time series behind every chart. A series is a list of [date, value] pairs,
 * oldest first.
 */
class DashboardPresenter
{
    private const PRICE_HISTORY_DAYS = 180;
    private const INDEX_HISTORY_DAYS = 730;
    private const COUNTRY_HISTORY_DAYS = 90;

    /** Country scores are daily model estimates and jump by ±10; charts show a trailing average. */
    private const COUNTRY_SMOOTHING_DAYS = 7;

    /** Indicator key => name of its history field in the API. */
    private const HISTORY_FIELDS = [
        'gold_price' => 'gold_price_history',
        'brent_crude' => 'brent_crude_history',
        'vix_fear_index' => 'vix_history',
        'global_economic_policy_uncertainty_index' => 'global_economic_policy_uncertainty_index_history',
    ];

    public function __construct(private readonly PriceFeed $prices)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Snapshot $snapshot): array
    {
        $date = $snapshot->last_updated->format('Y-m-d');
        $countryHistory = $this->countryHistory($date);

        $countries = $snapshot->countries->map(function ($c) use ($countryHistory) {
            $history = $countryHistory[self::countryKey($c->name)] ?? [];

            return [
                'name' => $c->name,
                'liquidity' => $c->liquidity,
                'logistics' => $c->logistics,
                'legitimacy' => $c->legitimacy,
                'overall' => $c->overall,
                'family_safety_note' => $c->family_safety_note ?? [],
                'liquidity_history' => $history['liquidity'] ?? [],
                'logistics_history' => $history['logistics'] ?? [],
                'legitimacy_history' => $history['legitimacy'] ?? [],
                'overall_history' => $history['overall'] ?? [],
            ];
        })->toArray();

        $scenarios = $snapshot->scenarios->map(fn ($s) => [
            'name' => $s->name,
            'description' => $s->description ?? [],
            'when_visible' => $s->when_visible,
            'earliest_date' => $s->earliest_date,
            'probability_percent' => $s->probability_percent,
        ])->toArray();

        return [
            'last_updated' => $date,
            'global_chaos_probability_100_percent' => $snapshot->global_chaos ?? [],
            'last_updated_news_titles' => $snapshot->last_updated_news_titles ?? [],
            'countries' => $countries,
            'scenarios' => $scenarios,
            'key_indicators_today' => $this->indicators($snapshot, $date),
        ];
    }

    /**
     * Where each country stood over the last week: the trailing average of its scores.
     * The ingest gives this to the model as the starting point for the new day.
     *
     * @return array<string, array{liquidity: float, logistics: float, legitimacy: float}> country name => scores
     */
    public function countryBaseline(string $date): array
    {
        $baseline = [];
        foreach ($this->countryHistory($date) as $name => $metrics) {
            $row = [];
            foreach (['liquidity', 'logistics', 'legitimacy'] as $metric) {
                $last = end($metrics[$metric]);
                if ($last === false) {
                    continue 2;
                }
                $row[$metric] = $last[1];
            }
            $baseline[$name] = $row;
        }

        return $baseline;
    }

    /**
     * @return array<string, mixed>
     */
    private function indicators(Snapshot $snapshot, string $date): array
    {
        $indicators = array_merge($snapshot->key_indicators ?? [], $this->prices->valuesOn($date));

        foreach (self::HISTORY_FIELDS as $series => $field) {
            $days = $series === 'global_economic_policy_uncertainty_index'
                ? self::INDEX_HISTORY_DAYS
                : self::PRICE_HISTORY_DAYS;
            $history = $this->prices->history($series, self::daysBefore($date, $days));
            $indicators[$field] = array_values(array_filter($history, fn ($p) => $p[0] <= $date));
        }

        $indicators['most_critical_shipping_chokepoint_status'] = $snapshot->shipping_chokepoint;

        return $indicators;
    }

    /**
     * Trailing average of every country metric, per day.
     *
     * @return array<string, array<string, array<int, array{0: string, 1: float}>>> country key => metric => series
     */
    private function countryHistory(string $date): array
    {
        $from = self::daysBefore($date, self::COUNTRY_HISTORY_DAYS);
        $rows = DB::table('countries')
            ->join('snapshots', 'snapshots.id', '=', 'countries.snapshot_id')
            ->whereDate('snapshots.last_updated', '>=', self::daysBefore($from, self::COUNTRY_SMOOTHING_DAYS - 1))
            ->whereDate('snapshots.last_updated', '<=', $date)
            ->orderBy('snapshots.last_updated')
            ->get([
                'snapshots.last_updated as day',
                'countries.name',
                'countries.liquidity',
                'countries.logistics',
                'countries.legitimacy',
                'countries.overall',
            ]);

        $byCountry = [];
        foreach ($rows as $row) {
            $byCountry[self::countryKey($row->name)][substr((string) $row->day, 0, 10)] = $row;
        }

        $result = [];
        foreach ($byCountry as $key => $days) {
            foreach (['liquidity', 'logistics', 'legitimacy', 'overall'] as $metric) {
                $series = [];
                foreach (array_keys($days) as $day) {
                    if ($day < $from) {
                        continue;
                    }
                    $windowStart = self::daysBefore($day, self::COUNTRY_SMOOTHING_DAYS - 1);
                    $window = [];
                    foreach ($days as $d => $r) {
                        if ($d >= $windowStart && $d <= $day && $r->{$metric} !== null) {
                            $window[] = (float) $r->{$metric};
                        }
                    }
                    if ($window !== []) {
                        $series[] = [$day, round(array_sum($window) / count($window), 1)];
                    }
                }
                $result[$key][$metric] = $series;
            }
        }

        return $result;
    }

    /**
     * The model writes the same country as "🇱🇻 Latvia" one day and "Latvia" the next.
     */
    private static function countryKey(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/^[^\p{L}]+/u', '', $name)));
    }

    private static function daysBefore(string $date, int $days): string
    {
        return (new \DateTimeImmutable($date))->modify("-{$days} days")->format('Y-m-d');
    }
}
