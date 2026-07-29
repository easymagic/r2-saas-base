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

include_once __DIR__ . '/Presentation/Http/Routes/web.php';

include_once __DIR__ . '/Infrastructure/boot.php';

$appServiceContainer->run($path, $method);
