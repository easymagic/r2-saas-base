<?php

use PlatformConfig\Presentation\PlatformConfigController;
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

            $route->get("platform-configs/migrate", [PlatformConfigController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("platform-configs", [PlatformConfigController::class, "all"]);
                $route->post("platform-configs/update", [PlatformConfigController::class, "update"]);
                $route->delete("platform-configs/{platform_config_id}/delete", [PlatformConfigController::class, "delete"]);
            });
        });
    });
});
