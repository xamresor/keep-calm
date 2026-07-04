<?php

namespace App\Http\Controllers;

use App\Models\EconomicIndicator;
use App\Models\MarketIndex;
use App\Models\MarketNews;
use App\Models\MarketStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MassiveDataController
{
    public function getIndices(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        $indices = MarketIndex::where('snapshot_date', $date)
            ->orderBy('ticker')
            ->get();

        return response()->json([
            'success' => true,
            'date' => $date,
            'count' => $indices->count(),
            'data' => $indices,
        ]);
    }

    public function getStocks(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $limit = min((int) $request->query('limit', 50), 100);

        $stocks = MarketStock::where('snapshot_date', $date)
            ->orderByDesc('change_percent')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'date' => $date,
            'count' => $stocks->count(),
            'data' => $stocks,
        ]);
    }

    public function getEconomicIndicators(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $limit = min((int) $request->query('limit', 50), 100);

        $query = EconomicIndicator::query();

        if ($type) {
            $query->where('indicator_type', $type);
        }

        $indicators = $query->orderByDesc('date')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'type' => $type,
            'count' => $indicators->count(),
            'data' => $indicators,
        ]);
    }

    public function getNews(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 20), 100);
        $ticker = $request->query('ticker');

        $query = MarketNews::query();

        if ($ticker) {
            $query->whereJsonContains('tickers', $ticker);
        }

        $news = $query->orderByDesc('published_utc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $news->count(),
            'data' => $news,
        ]);
    }

    public function getMarketOverview(): JsonResponse
    {
        $today = now()->format('Y-m-d');

        $indices = MarketIndex::where('snapshot_date', $today)->get();
        $topGainers = MarketStock::where('snapshot_date', $today)
            ->orderByDesc('change_percent')
            ->limit(10)
            ->get();
        $topLosers = MarketStock::where('snapshot_date', $today)
            ->orderBy('change_percent')
            ->limit(10)
            ->get();
        $latestNews = MarketNews::orderByDesc('published_utc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'date' => $today,
            'indices' => $indices,
            'top_gainers' => $topGainers,
            'top_losers' => $topLosers,
            'latest_news' => $latestNews,
        ]);
    }
}
