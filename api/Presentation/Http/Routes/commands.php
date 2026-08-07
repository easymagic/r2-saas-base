<?php

use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;
use Scafolder\Business\ScafolderServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) use ($appServiceContainer) {

     $route->command("test", function ($name) use ($appServiceContainer) {
      // $scafolderService = $appServiceContainer->container()->get(ScafolderServiceInterface::class);
      // $cls = $scafolderService->generateRepository($name);
      //   echo "Hello World: " . $name . "\n";
      //   echo $cls;
     });

});