<?php

use Wallet\Presentation\WalletController;
use Presentation\Http\Middlewares\GlobalApiAuthMiddleware;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use Presentation\Http\Middlewares\WalletFeedbackMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("wallet/migrate", [WalletController::class, "migrate"]);

            $route->middleware([
                GlobalApiAuthMiddleware::class
            ], function (RouteServiceInterface $route) {

                $route->middleware([
                    WalletFeedbackMiddleware::class
                ], function (RouteServiceInterface $route) {
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
            });
        });
    });
});
