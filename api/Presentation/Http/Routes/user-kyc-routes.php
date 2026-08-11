<?php

use UserKyc\Presentation\UserKycController;
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

            $route->get("user-kycs/migrate", [UserKycController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("user-kycs/my", [UserKycController::class, "fetchForUser"]);
                $route->post("user-kycs", [UserKycController::class, "create"]);
                $route->post("user-kycs/{kyc_id}", [UserKycController::class, "update"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("user-kycs/pending", [UserKycController::class, "fetchForApproval"]);
                    $route->get("user-kycs/approved", [UserKycController::class, "fetchApproved"]);
                    $route->get("user-kycs/rejected", [UserKycController::class, "fetchRejected"]);
                    $route->post("user-kycs/{kyc_id}/approve", [UserKycController::class, "approve"]);
                    $route->post("user-kycs/{kyc_id}/reject", [UserKycController::class, "reject"]);
                });
            });
        });
    });
});
