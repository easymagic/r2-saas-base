<?php

use Presentation\Http\Controllers\TestController;
use Presentation\Http\Controllers\User\UserController;
use Presentation\Http\Controllers\WalletController;
use Presentation\Http\Middlewares\GlobalApiAuthMiddleware;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
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

            $route->get('/test', function () {
                echo 'Hello World test...';
            });

            $route->prefix("auth", function (RouteServiceInterface $route) {

                $route->post("login", [UserController::class, "login"]);
                $route->post("register", [UserController::class, "register"]);
                // $route->post("refresh-token",[UserController::class,"refreshToken"]);
                // $route->post("refresh-otp",[UserController::class,"refreshOtp"]);



                $route->middleware([GlobalApiAuthMiddleware::class], function (RouteServiceInterface $route) {

                    $route->post("create", [UserController::class, "create"]);

                    $route->post("user/{user_id}", [UserController::class, "updateUser"]);
                    $route->delete("user/{user_id}", [UserController::class, "delete"]);
                    $route->post("me/{user_id}", [UserController::class, "updateProfile"]);
                    $route->post("me/change-password", [UserController::class, "changePassword"]);
                    $route->post("user/forgot-password", [UserController::class, "requestForgotPassword"]);
                    $route->post("user/reset-password", [UserController::class, "resetPassword"]);
                    $route->post("user/verify-email", [UserController::class, "verifyEmail"]);
                    $route->delete("login", [UserController::class, "logout"]);
                    $route->post("user/{user_id}/wallet-balance", [UserController::class, "getWalletBalance"]);
                });
            });


            $route->middleware([GlobalApiAuthMiddleware::class], function (RouteServiceInterface $route) {
                // Wallet routes
                $route->post("wallet/top-up-online", [WalletController::class, "topUpOnline"]);
                $route->post("wallet/top-up-manual", [WalletController::class, "topUpManual"]);
                $route->post("wallet/{wallet_id}/approve-manual-top-up", [WalletController::class, "approveManualTopUp"]);
                $route->post("wallet/{wallet_id}/reject-manual-top-up", [WalletController::class, "rejectManualTopUp"]);
                $route->get("wallet/my-pending-wallet-transactions", [WalletController::class, "myPendingWalletTransactions"]);
                $route->get("wallet/my-approved-wallet-transactions", [WalletController::class, "myApprovedWalletTransactions"]);
                $route->get("wallet/manual-pending-wallet-transactions", [WalletController::class, "manualPendingWalletTransactions"]);
                $route->get("wallet/manual-approved-wallet-transactions", [WalletController::class, "manualApprovedWalletTransactions"]);
            });

            $route->get("wallet/migrate", [WalletController::class, "migrate"]);

        });
    });
});
