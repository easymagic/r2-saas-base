<?php

use Product\Presentation\ProductController;
use Presentation\Http\Middlewares\GlobalApiAuthAdminMiddleware;
use Presentation\Http\Middlewares\GlobalApiAuthMiddleware;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use Presentation\Http\Middlewares\ProductCheckMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("products/migrate", [ProductController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("products", [ProductController::class, "fetchForFrontend"]);
                $route->get("products/slug/{slug}", [ProductController::class, "findBySlug"]);
                $route->get("products/uuid/{uuid}", [ProductController::class, "findByUuid"]);

                $route->middleware([
                    ProductCheckMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("products/merchant", [ProductController::class, "fetchForMerchant"]);
                    $route->post("products", [ProductController::class, "create"]);
                    $route->post("products/{product_id}", [ProductController::class, "update"]);
                    $route->delete("products/{product_id}", [ProductController::class, "remove"]);
                });

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("products/admin", [ProductController::class, "fetchForAdmin"]);
                });

                // After static `products/admin|slug|uuid` so `{product_id}` does not capture those
                $route->get("products/{product_id}", [ProductController::class, "findById"]);
            });
        });
    });
});
