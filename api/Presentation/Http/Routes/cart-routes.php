<?php

use Cart\Presentation\CartController;
use Presentation\Http\Middlewares\GlobalApiMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->middleware([GlobalApiMiddleware::class], function (RouteServiceInterface $route) {

        $route->prefix("v2", function (RouteServiceInterface $route) {

            $route->get("carts/migrate", [CartController::class, "migrate"]);
            $route->get("carts/uuid", [CartController::class, "generateCartUuid"]);

            $route->post("carts", [CartController::class, "addToCart"]);
            $route->get("carts/{uuid}", [CartController::class, "getCart"]);
            $route->delete("carts/{uuid}/products/{product_id}", [CartController::class, "removeFromCart"]);
            $route->delete("carts/{uuid}", [CartController::class, "clearCart"]);
        });
    });
});
