<?php

use Notification\Presentation\NotificationController;
use PlatformConfig\Presentation\PlatformConfigController;
use Presentation\Http\Controllers\ProxyOrderController;
use Presentation\Http\Controllers\TestController;
use Batch\Presentation\BatchController;
use ProxyOrderChangeLog\Presentation\ProxyOrderChangeLogController;
use SnappyOrder\Presentation\SnappyOrderController;
use Thread\Presentation\ThreadController;
use User\Presentation\UserController;
use Wallet\Presentation\WalletController;
use Log\Presentation\LogController;
use Category\Presentation\CategoryController;
use Product\Presentation\ProductController;
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
            $route->get("batches/migrate", [BatchController::class, "migrate"]);
            $route->get("threads/migrate", [ThreadController::class, "migrate"]);
            $route->get("proxy-order-change-logs/migrate", [ProxyOrderChangeLogController::class, "migrate"]);
            $route->get("logs/migrate", [LogController::class, "migrate"]);
            $route->get("categories/migrate", [CategoryController::class, "migrate"]);
            $route->get("products/migrate", [ProductController::class, "migrate"]);


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
                ], function (RouteServiceInterface $route) {

                    $route->post("create", [UserController::class, "create"]);

                    $route->post("user/{user_id}", [UserController::class, "updateUser"]);
                    $route->delete("user/{user_id}", [UserController::class, "delete"]);
                    $route->get("user/{user_id}", [UserController::class, "find"]);

                    $route->middleware([
                        WalletFeedbackMiddleware::class
                    ], function (RouteServiceInterface $route) {
                        $route->get("me", [UserController::class, "me"]);
                    });

                    $route->post("me", [UserController::class, "updateProfile"]);
                    $route->post("me/change-password", [UserController::class, "changePassword"]);
                    $route->delete("login", [UserController::class, "logout"]);
                    $route->post("me/wallet-balance", [UserController::class, "getWalletBalance"]);


                    $route->middleware([
                        GlobalApiAuthAdminMiddleware::class
                    ], function (RouteServiceInterface $route) {
                        $route->get("users", [UserController::class, "fetch"]);
                    });
                });
            });


            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {


                $route->middleware([
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
                });

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

                // Batch routes
                $route->get("batches", [BatchController::class, "getBatchList"]);
                $route->post("batches", [BatchController::class, "create"]);
                $route->delete("batches/{batch_id}", [BatchController::class, "remove"]);

                // Category routes
                $route->get("categories", [CategoryController::class, "fetchForFrontend"]);
                $route->get("categories/slug/{slug}", [CategoryController::class, "findBySlug"]);

                // Product routes
                $route->get("products", [ProductController::class, "fetchForFrontend"]);
                $route->get("products/merchant", [ProductController::class, "fetchForMerchant"]);
                $route->get("products/slug/{slug}", [ProductController::class, "findBySlug"]);
                $route->get("products/uuid/{uuid}", [ProductController::class, "findByUuid"]);

                // Thread routes
                $route->post("threads", [ThreadController::class, "createThread"]);
                $route->get("threads/{order_id}", [ThreadController::class, "getThreadListForOrder"]);

                $route->middleware([
                    GlobalApiAuthAdminMiddleware::class
                ], function (RouteServiceInterface $route) {
                    $route->post("snappy-orders/{order_id}/change-status", [SnappyOrderController::class, "changeStatus"]);
                    $route->post("snappy-orders/{order_id}/change-price", [SnappyOrderController::class, "changePrice"]);
                    $route->post("snappy-orders/{order_id}/assign-to-agent", [SnappyOrderController::class, "assignToAgent"]);
                    $route->post("snappy-orders/{order_id}/assign-to-batch", [SnappyOrderController::class, "assignToBatch"]);
                    $route->post("snappy-orders/{order_id}/unassign-from-batch", [SnappyOrderController::class, "unassignFromBatch"]);
                    $route->post("snappy-orders/publish-settings", [SnappyOrderController::class, "publishSettings"]);
                    $route->get("logs", [LogController::class, "fetch"]);

                    $route->get("categories/admin", [CategoryController::class, "fetchForAdmin"]);
                    $route->post("categories", [CategoryController::class, "create"]);
                    $route->post("categories/{category_id}", [CategoryController::class, "update"]);
                    $route->delete("categories/{category_id}", [CategoryController::class, "remove"]);

                    $route->get("products/admin", [ProductController::class, "fetchForAdmin"]);
                    $route->post("products", [ProductController::class, "create"]);
                    $route->post("products/{product_id}", [ProductController::class, "update"]);
                    $route->delete("products/{product_id}", [ProductController::class, "remove"]);

                });

                // After static `categories/admin` so `{category_id}` does not capture "admin"
                $route->get("categories/{category_id}", [CategoryController::class, "findById"]);
                // After static `products/admin|slug|uuid` so `{product_id}` does not capture those
                $route->get("products/{product_id}", [ProductController::class, "findById"]);
            });

            $route->get("wallet/migrate", [WalletController::class, "migrate"]);
        });
    });
});
