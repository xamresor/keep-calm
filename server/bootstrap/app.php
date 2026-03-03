<?php

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = dirname(__DIR__);
if (file_exists($basePath . '/.env')) {
    // Dotenv v5 API
    \Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false) {
            return $default;
        }
        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('app')) {
    function app($abstract = null, array $parameters = [])
    {
        $c = \Illuminate\Container\Container::getInstance();
        return $abstract ? $c->make($abstract, $parameters) : $c;
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, $default = null)
    {
        $config = app('config');
        return $key === null ? $config : $config->get($key, $default);
    }
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

$app = new Laravel\Lumen\Application($basePath);

$app->withFacades();
$app->withEloquent();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->configure('app');
$app->configure('database');
$app->configure('cache');
$app->configure('services');

$app->register(App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Database\MigrationServiceProvider::class);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
