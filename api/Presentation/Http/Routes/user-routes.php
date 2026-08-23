<?php

use Presentation\Http\Middlewares\EcomOrderFeedbackMiddleware;
use User\Presentation\UserController;
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

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("migrate", [UserController::class, "migrate"]);

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
                        WalletFeedbackMiddleware::class,
                        EcomOrderFeedbackMiddleware::class
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
        });
    });
});
