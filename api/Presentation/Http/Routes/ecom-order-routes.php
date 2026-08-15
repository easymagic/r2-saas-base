<?php

use EcomOrder\Presentation\EcomOrderController;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("ecom-orders/migrate", [EcomOrderController::class, "migrate"]);
        });
    });
});
