<?php

use Category\Presentation\CategoryController;
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

            $route->get("categories/migrate", [CategoryController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("categories", [CategoryController::class, "fetchForFrontend"]);
                $route->get("categories/slug/{slug}", [CategoryController::class, "findBySlug"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("categories/admin", [CategoryController::class, "fetchForAdmin"]);
                    $route->post("categories", [CategoryController::class, "create"]);
                    $route->post("categories/{category_id}", [CategoryController::class, "update"]);
                    $route->delete("categories/{category_id}", [CategoryController::class, "remove"]);
                });

                // After static `categories/admin` so `{category_id}` does not capture "admin"
                $route->get("categories/{category_id}", [CategoryController::class, "findById"]);
            });
        });
    });
});
