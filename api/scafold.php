<?php

use R2Packages\Framework\Infrastructure\Framework\Framework;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// $path = $_SERVER['REQUEST_URI'];
// $method = $_SERVER['REQUEST_METHOD'];

$framework = new Framework(__DIR__);
$appServiceContainer = $framework->boot();

$framework->getEnvService()->loadEnv(__DIR__ . '/.env');

include_once __DIR__ . '/Presentation/Http/Routes/commands.php';

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
$cmds = [];
$cmds["cool"] = "nice";
$command = trim($argv[1]);
$restArgs = [];
foreach ($argv as $key => $value) {
  if ($key > 1) {
    $restArgs[] = $value;
  }
}
$appServiceContainer->executeCommand($command, $restArgs);
// echo "Hello " . $cmds[$param1];
// $appServiceContainer->run($path, $method);
