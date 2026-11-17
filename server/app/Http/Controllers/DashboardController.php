<?php

namespace App\Http\Controllers;

use App\Contracts\DashboardCacheInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Services\DashboardPresenter;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    public function __construct(
        private readonly DashboardCacheInterface $cache,
        private readonly DashboardRepositoryInterface $repository,
        private readonly DashboardPresenter $presenter,
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

            $data = $this->presenter->present($snapshot);
            $this->cache->put($data);
        }

        return response()->json($data);
    }
}
