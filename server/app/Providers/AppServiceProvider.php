<?php

namespace App\Providers;

use App\Contracts\DashboardCacheInterface;
use App\Contracts\DashboardDataProviderInterface;
use App\Contracts\DashboardParserInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Contracts\IngestServiceInterface;
use App\Services\DashboardCache;
use App\Services\DashboardParser;
use App\Services\DashboardRepository;
use App\Services\FileDataProvider;
use App\Services\Gemini\GeminiDataProvider;
use App\Services\IngestService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DashboardParserInterface::class, DashboardParser::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardCacheInterface::class, DashboardCache::class);

        $this->app->bind(DashboardDataProviderInterface::class, function ($app) {
            if (config('services.gemini.api_key')) {
                $clientOptions = ['base_uri' => config('services.gemini.base_uri')];
                $caBundle = config('services.gemini.ca_bundle');
                if (is_string($caBundle) && $caBundle !== '') {
                    $clientOptions['verify'] = $caBundle;
                }

                return new GeminiDataProvider(
                    new Client($clientOptions),
                    config('services.gemini.api_key'),
                    config('services.gemini.model'),
                    config('services.gemini.base_uri'),
                );
            }
            $path = env('DASHBOARD_FILE') ?: base_path('storage/app/sample_dashboard.json');
            return new FileDataProvider($path);
        });

        $this->app->bind(IngestServiceInterface::class, IngestService::class);
    }
}
