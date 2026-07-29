<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Api\App;

$app = new App();
$app->run();
