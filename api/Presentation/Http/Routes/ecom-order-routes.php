<?php

use EcomOrder\Presentation\EcomOrderController;
use Presentation\Http\Middlewares\EcomOrderFeedbackMiddleware;
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

            $route->get("ecom-orders/migrate", [EcomOrderController::class, "migrate"]);

            $route->middleware([
                EcomOrderFeedbackMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->post("ecom-orders/checkout", [EcomOrderController::class, "checkout"]);
            });

            $route->middleware([
                GlobalApiAuthMiddleware::class,
                EcomOrderFeedbackMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("ecom-orders/my", [EcomOrderController::class, "fetchForUser"]);
                $route->get("ecom-orders/agent", [EcomOrderController::class, "fetchForAgent"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("ecom-orders/admin", [EcomOrderController::class, "fetchForAdmin"]);
                    $route->post("ecom-orders/pending-payments", [EcomOrderController::class, "getPendingPayments"]);
                    $route->post("ecom-orders/publish-settings", [EcomOrderController::class, "publishSettings"]);
                    $route->post("ecom-orders/{order_id}/delivery-status", [EcomOrderController::class, "updateDeliveryStatus"]);
                    $route->post("ecom-orders/{order_id}/assign-to-agent", [EcomOrderController::class, "assignToAgent"]);
                });

                $route->get("ecom-orders/{order_id}", [EcomOrderController::class, "getById"]);
            });
        });
    });
});
