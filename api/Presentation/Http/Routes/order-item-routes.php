<?php

use OrderItem\Presentation\OrderItemController;
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

            $route->get("order-items/migrate", [OrderItemController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->middleware([
                    ProductCheckMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("order-items/merchant", [OrderItemController::class, "fetchForMerchant"]);
                });

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("order-items/order/{order_id}", [OrderItemController::class, "fetchForOrder"]);
                    $route->post("order-items/{order_item_id}/settle", [OrderItemController::class, "settle"]);
                });
            });
        });
    });
});
