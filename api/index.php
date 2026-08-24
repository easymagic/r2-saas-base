<?php

define("MAIL_TEMPLATE_DIR", __DIR__ . '/mail_templates');

use R2Packages\Framework\Infrastructure\Framework\Framework;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$framework = new Framework(__DIR__);
$appServiceContainer = $framework->boot();

$framework->getEnvService()->loadEnv(__DIR__ . '/.env');

// Web UI + CLI commands only (JSON API routes removed).
include_once __DIR__ . '/Presentation/Http/Routes/web-routes.php';
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

$appServiceContainer->run($path, $method);
