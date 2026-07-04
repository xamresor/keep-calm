<?php

namespace App\Services\Massive;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MassiveApiClient
{
    private Client $client;
    private string $apiKey;
    private string $baseUri;

    public function __construct()
    {
        $this->apiKey = env('MASSIVE_API_KEY', '');
        $this->baseUri = env('MASSIVE_BASE_URI', 'https://api.massive.com');

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getMarketStatus(): array
    {
        return $this->request('GET', '/v3/reference/market-status');
    }

    public function getIndicesSnapshot(array $tickers = []): array
    {
        $params = [];
        if (!empty($tickers)) {
            $params['ticker.any_of'] = implode(',', $tickers);
        }

        return $this->request('GET', '/v3/snapshot/indices', $params);
    }

    public function getStocksSnapshot(array $tickers = []): array
    {
        $params = [];
        if (!empty($tickers)) {
            $params['tickers'] = implode(',', $tickers);
        }

        return $this->request('GET', '/v2/snapshot/locale/us/markets/stocks/tickers', $params);
    }

    public function getTopGainers(int $limit = 20): array
    {
        return $this->request('GET', '/v2/snapshot/locale/us/markets/stocks/gainers', [
            'limit' => $limit,
        ]);
    }

    public function getTopLosers(int $limit = 20): array
    {
        return $this->request('GET', '/v2/snapshot/locale/us/markets/stocks/losers', [
            'limit' => $limit,
        ]);
    }

    public function getTickerDetails(string $ticker): array
    {
        return $this->request('GET', "/v3/reference/tickers/{$ticker}");
    }

    public function getAllTickers(array $params = []): array
    {
        return $this->request('GET', '/v3/reference/tickers', $params);
    }

    public function getAggregates(
        string $ticker,
        int $multiplier,
        string $timespan,
        string $from,
        string $to,
        array $params = []
    ): array {
        return $this->request(
            'GET',
            "/v2/aggs/ticker/{$ticker}/range/{$multiplier}/{$timespan}/{$from}/{$to}",
            $params
        );
    }

    public function getPreviousClose(string $ticker): array
    {
        return $this->request('GET', "/v2/aggs/ticker/{$ticker}/prev");
    }

    public function getMarketHolidays(): array
    {
        return $this->request('GET', '/v1/marketstatus/upcoming');
    }

    public function getNews(array $params = []): array
    {
        return $this->request('GET', '/v2/reference/news', $params);
    }

    public function getDividends(string $ticker, array $params = []): array
    {
        return $this->request('GET', '/v3/reference/dividends', array_merge(['ticker' => $ticker], $params));
    }

    public function getSplits(string $ticker, array $params = []): array
    {
        return $this->request('GET', '/v3/reference/splits', array_merge(['ticker' => $ticker], $params));
    }

    public function getEconomicData(string $indicator, array $params = []): array
    {
        return $this->request('GET', "/v1/indicators/economy/{$indicator}", $params);
    }

    public function getTreasuryYields(array $params = []): array
    {
        return $this->request('GET', '/v1/indicators/economy/treasury-yields', $params);
    }

    public function getInflation(array $params = []): array
    {
        return $this->request('GET', '/v1/indicators/economy/inflation', $params);
    }

    public function getLaborMarket(array $params = []): array
    {
        return $this->request('GET', '/v1/indicators/economy/labor-market', $params);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('MASSIVE_API_KEY is not configured in .env file');
        }

        $params['apiKey'] = $this->apiKey;

        try {
            $response = $this->client->request($method, $endpoint, [
                'query' => $params,
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode JSON response: ' . json_last_error_msg());
            }

            if (isset($data['status']) && $data['status'] === 'ERROR') {
                throw new \RuntimeException($data['error'] ?? 'Unknown API error');
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Massive API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
