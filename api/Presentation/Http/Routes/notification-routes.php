<?php

use Notification\Presentation\NotificationController;
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

            $route->get("notifications/migrate", [NotificationController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {
                $route->get("notifications/my-notifications", [NotificationController::class, "myNotifications"]);
                $route->post("notifications/{notification_id}/mark-as-read", [NotificationController::class, "markAsRead"]);
                $route->post("notifications/{notification_id}/mark-as-unread", [NotificationController::class, "markAsUnread"]);
                $route->delete("notifications/{notification_id}/delete", [NotificationController::class, "delete"]);
            });
        });
    });
});
