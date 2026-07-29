<?php

use R2Packages\Framework\Infrastructure\Framework\Framework;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

define("DB_HOST", "127.0.0.1");
define("DB_NAME", "kanejitech_ecommerce_db4");
define("DB_USER", "root");
define("DB_PASSWORD", "");

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
