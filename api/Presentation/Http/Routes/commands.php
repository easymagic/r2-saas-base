<?php

use EcomOrder\Business\Usecases\GetPendingPaymentsService;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) use ($appServiceContainer) {

     $route->command("charge-bnpl", function () use ($appServiceContainer) {
        $getPendingPaymentsService = $appServiceContainer->container()->get(GetPendingPaymentsService::class);
        $query = $getPendingPaymentsService->execute();
        echo "Pending ecom payments: " . $query->count() . "\n";
     });

});
