<?php

use Log\Presentation\LogController;
use Presentation\Http\Middlewares\GlobalApiAuthAdminMiddleware;
use Presentation\Http\Middlewares\GlobalApiAuthMiddleware;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("logs/migrate", [LogController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("logs", [LogController::class, "fetch"]);
                });
            });
        });
    });
});
