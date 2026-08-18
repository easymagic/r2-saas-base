<?php

use BnplPaymentSchedule\Presentation\BnplPaymentScheduleController;
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

            $route->get("bnpl-payment-schedules/migrate", [BnplPaymentScheduleController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class,
                GlobalApiAuthAdminMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("bnpl-payment-schedules/order/{order_id}/first", [BnplPaymentScheduleController::class, "getFirstSchedule"]);
                $route->get("bnpl-payment-schedules/order/{order_id}/next", [BnplPaymentScheduleController::class, "getNextSchedule"]);
                $route->post("bnpl-payment-schedules/{schedule_id}/charge", [BnplPaymentScheduleController::class, "chargeSchedule"]);
            });
        });
    });
});
