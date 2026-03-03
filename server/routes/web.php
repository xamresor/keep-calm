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
});
