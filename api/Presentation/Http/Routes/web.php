<?php

use Notification\Presentation\NotificationController;
use PlatformConfig\Presentation\PlatformConfigController;
use Presentation\Http\Controllers\ProxyOrderController;
use Presentation\Http\Controllers\TestController;
use SnappyOrder\Presentation\SnappyOrderController;
use User\Presentation\UserController;
use Wallet\Presentation\WalletController;
use Presentation\Http\Middlewares\GlobalApiAuthAdminMiddleware;
use Presentation\Http\Middlewares\GlobalApiAuthMiddleware;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use Presentation\Http\Middlewares\WalletFeedbackMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->get("test-env", [TestController::class, "index"]);


    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("migrate", [UserController::class, "migrate"]);
            $route->get("notifications/migrate", [NotificationController::class, "migrate"]);
            $route->get("platform-configs/migrate", [PlatformConfigController::class, "migrate"]);
            $route->get("snappy-orders/migrate", [SnappyOrderController::class, "migrate"]);


            $route->get('/test', function () {
                echo 'Hello World test...';
            });

            $route->prefix("auth", function (RouteServiceInterface $route) {

                $route->post("login", [UserController::class, "login"]);
                $route->post("register", [UserController::class, "register"]);
                // $route->post("refresh-token",[UserController::class,"refreshToken"]);
                // $route->post("refresh-otp",[UserController::class,"refreshOtp"]);

                $route->post("user/forgot-password", [UserController::class, "requestForgotPassword"]);
                $route->post("user/reset-password", [UserController::class, "resetPassword"]);
                $route->post("user/verify-email", [UserController::class, "verifyEmail"]);


                $route->middleware([
                    GlobalApiAuthMiddleware::class,
                    WalletFeedbackMiddleware::class
                ], function (RouteServiceInterface $route) {

                    $route->post("create", [UserController::class, "create"]);

                    $route->post("user/{user_id}", [UserController::class, "updateUser"]);
                    $route->delete("user/{user_id}", [UserController::class, "delete"]);
                    $route->get("user/{user_id}", [UserController::class, "find"]);

                    $route->post("me", [UserController::class, "updateProfile"]);
                    $route->post("me/change-password", [UserController::class, "changePassword"]);
                    $route->delete("login", [UserController::class, "logout"]);
                    $route->post("me/wallet-balance", [UserController::class, "getWalletBalance"]);
                    $route->get("me", [UserController::class, "me"]);
                });
            });


            $route->middleware([
                GlobalApiAuthMiddleware::class,
                WalletFeedbackMiddleware::class
            ], function (RouteServiceInterface $route) {
                // Wallet routes
                $route->post("wallet/top-up-online", [WalletController::class, "topUpOnline"]);
                $route->post("wallet/top-up-manual", [WalletController::class, "topUpManual"]);
                $route->post("wallet/{wallet_id}/approve-manual-top-up", [WalletController::class, "approveManualTopUp"]);
                $route->post("wallet/{wallet_id}/reject-manual-top-up", [WalletController::class, "rejectManualTopUp"]);
                $route->get("wallet/my-pending-wallet-transactions", [WalletController::class, "myPendingWalletTransactions"]);
                $route->get("wallet/my-approved-wallet-transactions", [WalletController::class, "myApprovedWalletTransactions"]);
                $route->get("wallet/manual-pending-wallet-transactions", [WalletController::class, "manualPendingWalletTransactions"]);
                $route->get("wallet/manual-approved-wallet-transactions", [WalletController::class, "manualApprovedWalletTransactions"]);
                $route->get("wallet/manual-rejected-wallet-transactions", [WalletController::class, "manualRejectedWalletTransactions"]);

                // Notifications routes
                $route->get("notifications/my-notifications", [NotificationController::class, "myNotifications"]);
                $route->post("notifications/{notification_id}/mark-as-read", [NotificationController::class, "markAsRead"]);
                $route->post("notifications/{notification_id}/mark-as-unread", [NotificationController::class, "markAsUnread"]);
                $route->delete("notifications/{notification_id}/delete", [NotificationController::class, "delete"]);


                // Platform config routes
                $route->get("platform-configs", [PlatformConfigController::class, "all"]);
                $route->post("platform-configs/update", [PlatformConfigController::class, "update"]);
                $route->delete("platform-configs/{platform_config_id}/delete", [PlatformConfigController::class, "delete"]);


                // Snappy order routes
                $route->post("snappy-orders", [SnappyOrderController::class, "create"]);
                $route->get("snappy-orders/my-orders", [SnappyOrderController::class, "getMyOrdersAsCustomer"]);
                $route->get("snappy-orders/agent-orders", [SnappyOrderController::class, "getMyOrdersAsAgent"]);
                $route->post("snappy-orders/{order_id}/pay-from-wallet", [SnappyOrderController::class, "payOrderFromWallet"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->get("snappy-orders/admin-orders", [SnappyOrderController::class, "getMyOrderAsAdmin"]);
                    $route->post("snappy-orders/{order_id}/change-status", [SnappyOrderController::class, "changeStatus"]);
                    $route->post("snappy-orders/{order_id}/assign-to-agent", [SnappyOrderController::class, "assignToAgent"]);
                    $route->post("snappy-orders/publish-settings", [SnappyOrderController::class, "publishSettings"]);
                });
            });

            $route->get("wallet/migrate", [WalletController::class, "migrate"]);
        });
    });
});
