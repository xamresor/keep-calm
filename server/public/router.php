<?php

/**
 * Router for PHP built-in server: route non-file requests to index.php
 * so that /api/dashboard and other app routes work.
 *
 * Run: php -S localhost:8000 -t public public/router.php
 * (from the project root, i.e. server/)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing files (e.g. index.html, assets) as-is
if ($uri !== '/' && $uri !== '' && file_exists(__DIR__ . $uri)) {
    return false;
}

// So the app sees the request as handled by index.php and path = $uri
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
$_SERVER['PATH_INFO'] = $uri ?: '/';

require __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
