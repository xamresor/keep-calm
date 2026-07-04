<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router->get('/', function () {
    $path = base_path('public/index.html');
    if (!is_file($path)) {
        return response()->json(['app' => 'Keep Calm API', 'docs' => 'GET /api/dashboard']);
    }
    return response()->file($path, ['Content-Type' => 'text/html; charset=UTF-8']);
});

$router->group(['prefix' => 'api'], function (Router $router) {
    $router->get('dashboard', ['uses' => 'DashboardController@index']);

    $router->group(['prefix' => 'market'], function (Router $router) {
        $router->get('overview', ['uses' => 'MassiveDataController@getMarketOverview']);
        $router->get('indices', ['uses' => 'MassiveDataController@getIndices']);
        $router->get('stocks', ['uses' => 'MassiveDataController@getStocks']);
        $router->get('economy', ['uses' => 'MassiveDataController@getEconomicIndicators']);
        $router->get('news', ['uses' => 'MassiveDataController@getNews']);
    });
});
