<?php

/**
 * Router for PHP built-in server (`composer serve`).
 * Forwards non-static requests to index.php so module folders
 * (Cart/, User/, Wallet/, …) do not shadow web routes.
 */
$path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$path = $path === null || $path === '' ? '/' : $path;

$staticPrefixes = [
    '/uploads/',
];

foreach ($staticPrefixes as $prefix) {
    if (strpos($path, $prefix) === 0) {
        $file = __DIR__ . $path;
        if (is_file($file)) {
            return false;
        }
        break;
    }
}

if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
