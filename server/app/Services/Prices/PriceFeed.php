<?php

namespace App\Services\Prices;

use App\Models\PricePoint;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Real market data for the key indicators. The language model must not be the
 * source of prices: it invents them. Daily closes come from the Yahoo Finance
 * chart endpoint, the monthly Global Economic Policy Uncertainty index from FRED.
 */
class PriceFeed
{
    /** Indicator key (as stored in snapshots.key_indicators) => Yahoo symbol. */
    public const YAHOO_SERIES = [
        'gold_price' => 'GC=F',
        'brent_crude' => 'BZ=F',
        'vix_fear_index' => '^VIX',
    ];

    /** Indicator key => FRED series id. */
    public const FRED_SERIES = [
        'global_economic_policy_uncertainty_index' => 'GEPUCURRENT',
    ];

    private const YAHOO_URL = 'https://query1.finance.yahoo.com/v8/finance/chart/%s';
    private const FRED_URL = 'https://fred.stlouisfed.org/graph/fredgraph.csv';

    public function __construct(private readonly Client $client)
    {
    }

    /** @return string[] */
    public static function seriesKeys(): array
    {
        return array_merge(array_keys(self::YAHOO_SERIES), array_keys(self::FRED_SERIES));
    }

    /**
     * Download every series and upsert it. One failed series does not stop the others.
     *
     * @return array<string, int|string> series => stored point count, or the error text
     */
    public function refresh(string $range = '1mo'): array
    {
        $report = [];

        foreach (self::YAHOO_SERIES as $series => $symbol) {
            $report[$series] = $this->attempt(fn () => $this->store($series, $this->fetchYahoo($symbol, $range)));
        }
        foreach (self::FRED_SERIES as $series => $id) {
            $report[$series] = $this->attempt(fn () => $this->store($series, $this->fetchFred($id)));
        }

        return $report;
    }

    /**
     * Latest stored value of each series on or before the date.
     *
     * @return array<string, float>
     */
    public function valuesOn(string $date): array
    {
        $values = [];
        foreach (self::seriesKeys() as $series) {
            $point = PricePoint::query()
                ->where('series', $series)
                ->whereDate('date', '<=', $date)
                ->orderByDesc('date')
                ->first();
            if ($point) {
                $values[$series] = $point->value;
            }
        }

        return $values;
    }

    /**
     * @return array<int, array{0: string, 1: float}> [date, value] pairs, oldest first
     */
    public function history(string $series, string $from): array
    {
        return PricePoint::query()
            ->where('series', $series)
            ->whereDate('date', '>=', $from)
            ->orderBy('date')
            ->get()
            ->map(fn (PricePoint $p) => [substr((string) $p->date, 0, 10), $p->value])
            ->all();
    }

    /**
     * @return array<string, float> date => close
     */
    private function fetchYahoo(string $symbol, string $range): array
    {
        $response = $this->client->get(sprintf(self::YAHOO_URL, rawurlencode($symbol)), [
            'query' => ['range' => $range, 'interval' => '1d'],
            'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; keep-calm-dashboard)'],
            'timeout' => 20,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        $result = $data['chart']['result'][0] ?? null;
        $timestamps = $result['timestamp'] ?? null;
        $closes = $result['indicators']['quote'][0]['close'] ?? null;
        if (!is_array($timestamps) || !is_array($closes)) {
            throw new RuntimeException("Unexpected Yahoo response for {$symbol}");
        }

        // Day boundaries follow the exchange's own clock, not UTC.
        $offset = (int) ($result['meta']['gmtoffset'] ?? 0);
        $points = [];
        foreach ($timestamps as $i => $ts) {
            $close = $closes[$i] ?? null;
            if (!is_numeric($close) || $close <= 0) {
                continue;
            }
            $points[gmdate('Y-m-d', (int) $ts + $offset)] = round((float) $close, 2);
        }
        if ($points === []) {
            throw new RuntimeException("No prices in Yahoo response for {$symbol}");
        }

        return $points;
    }

    /**
     * @return array<string, float> date => value
     */
    private function fetchFred(string $id): array
    {
        $response = $this->client->get(self::FRED_URL, [
            'query' => ['id' => $id, 'cosd' => date('Y-m-d', strtotime('-3 years'))],
            'timeout' => 20,
        ]);
        $points = [];
        foreach (preg_split('/\R/', trim((string) $response->getBody())) as $line) {
            [$date, $value] = array_pad(explode(',', $line), 2, null);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) && is_numeric($value)) {
                $points[$date] = round((float) $value, 1);
            }
        }
        if ($points === []) {
            throw new RuntimeException("No values in FRED response for {$id}");
        }

        return $points;
    }

    /**
     * @param array<string, float> $points
     */
    private function store(string $series, array $points): int
    {
        DB::transaction(function () use ($series, $points) {
            foreach ($points as $date => $value) {
                PricePoint::updateOrCreate(['series' => $series, 'date' => $date], ['value' => $value]);
            }
        });

        return count($points);
    }

    private function attempt(callable $work): int|string
    {
        try {
            return $work();
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
