<?php

use R2Packages\Framework\Infrastructure\Framework\Framework;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$framework = new Framework(__DIR__);
$appServiceContainer = $framework->boot();

$framework->getEnvService()->loadEnv(__DIR__ . '/.env');

$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {
    $route->get('/', function () {
        echo  'Hello World...';
    });
});

// scan routes directory
$routes = glob(__DIR__ . '/Presentation/Http/Routes/*.php');

foreach ($routes as $route) {
    include_once $route;
}

// include_once __DIR__ . '/Presentation/Http/Routes/web.php';

/**
 * Boots
 */
$boots = [
    'boot' => __DIR__ . '/Kernel/boot.php',
    'boot_extend' => __DIR__ . '/Kernel/boot_extend.php',
];

foreach ($boots as $boot) {
    include_once $boot;
}

$appServiceContainer->run($path, $method);
