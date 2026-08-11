<?php

use SnappyOrder\Presentation\SnappyOrderController;
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

            $route->get("snappy-orders/migrate", [SnappyOrderController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("snappy-orders/admin-orders", [SnappyOrderController::class, "getMyOrderAsAdmin"]);
                });

                $route->post("snappy-orders", [SnappyOrderController::class, "create"]);
                $route->get("snappy-orders/my-orders", [SnappyOrderController::class, "getMyOrdersAsCustomer"]);
                $route->get("snappy-orders/agent-orders", [SnappyOrderController::class, "getMyOrdersAsAgent"]);
                $route->get("snappy-orders/{order_id}", [SnappyOrderController::class, "getById"]);
                $route->post("snappy-orders/{order_id}/pay-from-wallet", [SnappyOrderController::class, "payOrderFromWallet"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->post("snappy-orders/{order_id}/change-status", [SnappyOrderController::class, "changeStatus"]);
                    $route->post("snappy-orders/{order_id}/change-price", [SnappyOrderController::class, "changePrice"]);
                    $route->post("snappy-orders/{order_id}/assign-to-agent", [SnappyOrderController::class, "assignToAgent"]);
                    $route->post("snappy-orders/{order_id}/assign-to-batch", [SnappyOrderController::class, "assignToBatch"]);
                    $route->post("snappy-orders/{order_id}/unassign-from-batch", [SnappyOrderController::class, "unassignFromBatch"]);
                    $route->post("snappy-orders/publish-settings", [SnappyOrderController::class, "publishSettings"]);
                });
            });
        });
    });
});
