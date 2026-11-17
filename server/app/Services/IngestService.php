<?php

namespace App\Services;

use App\Contracts\DashboardCacheInterface;
use App\Contracts\DashboardDataProviderInterface;
use App\Contracts\DashboardParserInterface;
use App\Contracts\IngestServiceInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Services\News\NewsFeed;
use App\Services\Prices\PriceFeed;

class IngestService implements IngestServiceInterface
{
    public function __construct(
        private readonly DashboardDataProviderInterface $provider,
        private readonly DashboardParserInterface $parser,
        private readonly DashboardRepositoryInterface $repository,
        private readonly DashboardCacheInterface $cache,
        private readonly PriceFeed $prices,
        private readonly DashboardPresenter $presenter,
        private readonly NewsFeed $news,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function run(string $when = 'now'): array
    {
        try {
            $targetDate = $this->resolveTargetDate($when);

            // Prices come from the market feed, never from the model: it invents them.
            // A failed feed keeps the last stored real values.
            $this->prices->refresh();
            $real = $this->prices->valuesOn($targetDate);

            $previous = $this->repository->getLatest();
            $raw = $this->provider->fetch($when, [
                'market' => $real,
                // Real headlines, only for a run about today: feeds do not go back in time.
                'headlines' => $targetDate === gmdate('Y-m-d') ? $this->headlines() : [],
                // The model has no memory of yesterday. Without a starting point the scores jump by ±10 a day.
                'baseline_countries' => $this->presenter->countryBaseline($targetDate),
                'baseline_chaos' => $previous ? array_intersect_key(
                    $previous->global_chaos ?? [],
                    ['probability_from' => 1, 'probability_to' => 1],
                ) : [],
            ]);
            $parsed = $this->parser->parse($raw);
            $parsed['snapshot']['last_updated'] = $targetDate;
            $parsed['snapshot']['key_indicators'] = array_merge(
                array_fill_keys(PriceFeed::seriesKeys(), null),
                $real,
            );
            $snapshot = $this->repository->store($parsed);
            $this->cache->put($this->presenter->present($snapshot));

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

    /**
     * @return array<int, array{source: string, title: string, time: string}>
     */
    private function headlines(): array
    {
        try {
            return $this->news->refresh();
        } catch (\Throwable) {
            return [];
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
}
