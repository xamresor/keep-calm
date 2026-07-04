<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicator;
use App\Models\MarketIndex;
use App\Models\MarketNews;
use App\Models\MarketStock;
use App\Services\Massive\MassiveApiClient;
use Illuminate\Console\Command;

class IngestMassiveDataCommand extends Command
{
    protected $signature = 'massive:ingest
                            {--indices : Ingest market indices data}
                            {--stocks : Ingest stocks data}
                            {--economy : Ingest economic indicators}
                            {--news : Ingest market news}
                            {--all : Ingest all data types}';

    protected $description = 'Fetch market data from Massive.com API and store in database';

    private MassiveApiClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new MassiveApiClient();
    }

    public function handle(): int
    {
        $this->info('Starting Massive.com data ingest...');

        $ingestAll = $this->option('all');

        try {
            if ($ingestAll || $this->option('indices')) {
                $this->ingestIndices();
            }

            if ($ingestAll || $this->option('stocks')) {
                $this->ingestStocks();
            }

            if ($ingestAll || $this->option('economy')) {
                $this->ingestEconomicIndicators();
            }

            if ($ingestAll || $this->option('news')) {
                $this->ingestNews();
            }

            if (!$ingestAll && !$this->option('indices') && !$this->option('stocks')
                && !$this->option('economy') && !$this->option('news')) {
                $this->warn('No data type specified. Use --all or specify --indices, --stocks, --economy, or --news');
                return self::FAILURE;
            }

            $this->info('Massive.com data ingest completed successfully!');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Ingest failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function ingestIndices(): void
    {
        $this->info('Ingesting market indices...');

        $majorIndices = [
            'I:SPX',   // S&P 500
            'I:DJI',   // Dow Jones
            'I:NDX',   // Nasdaq 100
            'I:RUT',   // Russell 2000
            'I:VIX',   // VIX
        ];

        $response = $this->client->getIndicesSnapshot($majorIndices);

        if (!isset($response['results']) || !is_array($response['results'])) {
            $this->warn('No indices data returned from API');
            return;
        }

        $snapshotDate = now()->format('Y-m-d');
        $count = 0;

        foreach ($response['results'] as $index) {
            $data = [
                'ticker' => $index['ticker'] ?? null,
                'name' => $index['name'] ?? null,
                'market_status' => $index['market_status'] ?? 'unknown',
                'value' => $index['value'] ?? null,
                'change' => ($index['session']['change'] ?? null),
                'change_percent' => ($index['session']['change_percent'] ?? null),
                'open' => ($index['session']['open'] ?? null),
                'high' => ($index['session']['high'] ?? null),
                'low' => ($index['session']['low'] ?? null),
                'close' => ($index['session']['close'] ?? null),
                'previous_close' => ($index['session']['previous_close'] ?? null),
                'updated_at_source' => isset($index['updated'])
                    ? date('Y-m-d H:i:s', $index['updated'] / 1000000000)
                    : null,
                'snapshot_date' => $snapshotDate,
            ];

            MarketIndex::updateOrCreate(
                ['ticker' => $data['ticker'], 'snapshot_date' => $snapshotDate],
                $data
            );

            $count++;
        }

        $this->info("Ingested {$count} market indices");
    }

    private function ingestStocks(): void
    {
        $this->info('Ingesting top market movers...');

        $snapshotDate = now()->format('Y-m-d');
        $count = 0;

        $gainers = $this->client->getTopGainers(20);
        if (isset($gainers['tickers']) && is_array($gainers['tickers'])) {
            foreach ($gainers['tickers'] as $stock) {
                $this->storeStock($stock, $snapshotDate);
                $count++;
            }
        }

        $losers = $this->client->getTopLosers(20);
        if (isset($losers['tickers']) && is_array($losers['tickers'])) {
            foreach ($losers['tickers'] as $stock) {
                $this->storeStock($stock, $snapshotDate);
                $count++;
            }
        }

        $this->info("Ingested {$count} stocks");
    }

    private function storeStock(array $stock, string $snapshotDate): void
    {
        $data = [
            'ticker' => $stock['ticker'] ?? null,
            'name' => null,
            'market' => $stock['market'] ?? null,
            'locale' => $stock['locale'] ?? null,
            'primary_exchange' => $stock['primary_exchange'] ?? null,
            'type' => $stock['type'] ?? null,
            'currency_name' => null,
            'price' => ($stock['day']['c'] ?? null),
            'change' => ($stock['todaysChange'] ?? null),
            'change_percent' => ($stock['todaysChangePerc'] ?? null),
            'open' => ($stock['day']['o'] ?? null),
            'high' => ($stock['day']['h'] ?? null),
            'low' => ($stock['day']['l'] ?? null),
            'close' => ($stock['day']['c'] ?? null),
            'previous_close' => ($stock['prevDay']['c'] ?? null),
            'volume' => ($stock['day']['v'] ?? null),
            'vwap' => ($stock['day']['vw'] ?? null),
            'updated_at_source' => isset($stock['updated'])
                ? date('Y-m-d H:i:s', $stock['updated'] / 1000000000)
                : null,
            'snapshot_date' => $snapshotDate,
        ];

        MarketStock::updateOrCreate(
            ['ticker' => $data['ticker'], 'snapshot_date' => $snapshotDate],
            $data
        );
    }

    private function ingestEconomicIndicators(): void
    {
        $this->info('Ingesting economic indicators...');

        $count = 0;

        try {
            $inflation = $this->client->getInflation(['limit' => 10]);
            if (isset($inflation['results']) && is_array($inflation['results'])) {
                foreach ($inflation['results'] as $item) {
                    EconomicIndicator::updateOrCreate(
                        [
                            'indicator_type' => 'inflation',
                            'indicator_name' => $item['name'] ?? 'Unknown',
                            'date' => $item['date'] ?? now()->format('Y-m-d'),
                        ],
                        [
                            'value' => $item['value'] ?? null,
                            'unit' => $item['unit'] ?? null,
                            'metadata' => $item,
                        ]
                    );
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            $this->warn('Failed to fetch inflation data: ' . $e->getMessage());
        }

        try {
            $labor = $this->client->getLaborMarket(['limit' => 10]);
            if (isset($labor['results']) && is_array($labor['results'])) {
                foreach ($labor['results'] as $item) {
                    EconomicIndicator::updateOrCreate(
                        [
                            'indicator_type' => 'labor_market',
                            'indicator_name' => $item['name'] ?? 'Unknown',
                            'date' => $item['date'] ?? now()->format('Y-m-d'),
                        ],
                        [
                            'value' => $item['value'] ?? null,
                            'unit' => $item['unit'] ?? null,
                            'metadata' => $item,
                        ]
                    );
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            $this->warn('Failed to fetch labor market data: ' . $e->getMessage());
        }

        try {
            $treasury = $this->client->getTreasuryYields(['limit' => 10]);
            if (isset($treasury['results']) && is_array($treasury['results'])) {
                foreach ($treasury['results'] as $item) {
                    EconomicIndicator::updateOrCreate(
                        [
                            'indicator_type' => 'treasury_yields',
                            'indicator_name' => $item['name'] ?? 'Unknown',
                            'date' => $item['date'] ?? now()->format('Y-m-d'),
                        ],
                        [
                            'value' => $item['value'] ?? null,
                            'unit' => $item['unit'] ?? null,
                            'metadata' => $item,
                        ]
                    );
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            $this->warn('Failed to fetch treasury yields data: ' . $e->getMessage());
        }

        $this->info("Ingested {$count} economic indicators");
    }

    private function ingestNews(): void
    {
        $this->info('Ingesting market news...');

        $response = $this->client->getNews([
            'limit' => 50,
            'order' => 'desc',
        ]);

        if (!isset($response['results']) || !is_array($response['results'])) {
            $this->warn('No news data returned from API');
            return;
        }

        $count = 0;

        foreach ($response['results'] as $article) {
            $data = [
                'article_id' => $article['id'] ?? null,
                'publisher' => ($article['publisher']['name'] ?? null),
                'title' => $article['title'] ?? null,
                'author' => $article['author'] ?? null,
                'published_utc' => isset($article['published_utc'])
                    ? date('Y-m-d H:i:s', strtotime($article['published_utc']))
                    : null,
                'article_url' => $article['article_url'] ?? null,
                'image_url' => $article['image_url'] ?? null,
                'description' => $article['description'] ?? null,
                'keywords' => $article['keywords'] ?? [],
                'tickers' => $article['tickers'] ?? [],
                'insights' => $article['insights'] ?? [],
            ];

            if ($data['article_id']) {
                MarketNews::updateOrCreate(
                    ['article_id' => $data['article_id']],
                    $data
                );
                $count++;
            }
        }

        $this->info("Ingested {$count} news articles");
    }
}
