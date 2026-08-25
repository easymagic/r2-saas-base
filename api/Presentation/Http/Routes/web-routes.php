<?php

use Presentation\Http\Controllers\Web\AdminWebController;
use Presentation\Http\Controllers\Web\AuthWebController;
use Presentation\Http\Controllers\Web\BatchWebController;
use Presentation\Http\Controllers\Web\CartWebController;
use Presentation\Http\Controllers\Web\CategoryWebController;
use Presentation\Http\Controllers\Web\DashboardWebController;
use Presentation\Http\Controllers\Web\EcomOrderWebController;
use Presentation\Http\Controllers\Web\LogWebController;
use Presentation\Http\Controllers\Web\MigrationsWebController;
use Presentation\Http\Controllers\Web\NotificationWebController;
use Presentation\Http\Controllers\Web\OrdersWebController;
use Presentation\Http\Controllers\Web\PlatformConfigWebController;
use Presentation\Http\Controllers\Web\ProductWebController;
use Presentation\Http\Controllers\Web\UserKycWebController;
use Presentation\Http\Controllers\Web\UserWebController;
use Presentation\Http\Controllers\Web\WalletWebController;
use Presentation\Http\Middlewares\EcomOrderFeedbackMiddleware;
use Presentation\Http\Middlewares\WalletFeedbackMiddleware;
use Presentation\Http\Middlewares\WebAdminMiddleware;
use Presentation\Http\Middlewares\WebAuthMiddleware;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Router\RouteServiceInterface;

/**
 * Web UI routes (classic PHP pages).
 *
 * @var AppServiceContainer $appServiceContainer
 */
$appServiceContainer->loadRoutes(function (RouteServiceInterface $route) {

    $route->get('/', [AuthWebController::class, 'home']);
    $route->get('login', [AuthWebController::class, 'showLogin']);
    $route->post('login', [AuthWebController::class, 'login']);
    $route->get('register', [AuthWebController::class, 'showRegister']);
    $route->post('register', [AuthWebController::class, 'register']);
    $route->get('register/verify-otp', [AuthWebController::class, 'showVerifyOtp']);
    $route->post('register/verify-otp', [AuthWebController::class, 'verifyOtp']);
    $route->get('forgot-password', [UserWebController::class, 'showForgotPassword']);
    $route->post('forgot-password', [UserWebController::class, 'forgotPassword']);
    $route->get('reset-password', [UserWebController::class, 'showResetPassword']);
    $route->post('reset-password', [UserWebController::class, 'resetPassword']);
    $route->post('logout', [AuthWebController::class, 'logout']);
    $route->get('logout', [AuthWebController::class, 'logout']);

    // Unauthenticated: run every module migration (GET only).
    $route->get('migrations', [MigrationsWebController::class, 'run']);

    $route->middleware([WebAuthMiddleware::class, WalletFeedbackMiddleware::class, EcomOrderFeedbackMiddleware::class], function (RouteServiceInterface $route) {
        $route->get('dashboard', [DashboardWebController::class, 'index']);

        $route->get('profile', [UserWebController::class, 'profile']);
        $route->post('profile', [UserWebController::class, 'updateProfile']);
        $route->post('profile/change-password', [UserWebController::class, 'changePassword']);

        $route->get('notifications', [NotificationWebController::class, 'index']);
        $route->post('notifications/{notification_id}/read', [NotificationWebController::class, 'markRead']);
        $route->post('notifications/{notification_id}/unread', [NotificationWebController::class, 'markUnread']);
        $route->post('notifications/{notification_id}/delete', [NotificationWebController::class, 'delete']);

        $route->get('orders', [OrdersWebController::class, 'index']);
        $route->get('orders/create', [OrdersWebController::class, 'createForm']);
        $route->post('orders', [OrdersWebController::class, 'store']);
        $route->get('orders/{order_id}', [OrdersWebController::class, 'show']);
        $route->post('orders/{order_id}/pay', [OrdersWebController::class, 'payFromWallet']);
        $route->post('orders/{order_id}/status', [OrdersWebController::class, 'changeStatus']);
        $route->post('orders/{order_id}/price', [OrdersWebController::class, 'changePrice']);
        $route->post('orders/{order_id}/assign-agent', [OrdersWebController::class, 'assignAgent']);
        $route->post('orders/{order_id}/assign-batch', [OrdersWebController::class, 'assignBatch']);
        $route->post('orders/{order_id}/unassign-batch', [OrdersWebController::class, 'unassignBatch']);
        $route->post('orders/{order_id}/thread', [OrdersWebController::class, 'postThread']);

        $route->get('wallet', [WalletWebController::class, 'index']);
        $route->post('wallet/top-up-manual', [WalletWebController::class, 'topUpManual']);
        $route->post('wallet/top-up-online', [WalletWebController::class, 'topUpOnline']);

        $route->get('shop', [ProductWebController::class, 'shop']);
        $route->get('shop/products/{product_id}', [ProductWebController::class, 'shopShow']);

        $route->get('cart', [CartWebController::class, 'index']);
        $route->post('cart/add', [CartWebController::class, 'add']);
        $route->post('cart/remove', [CartWebController::class, 'remove']);
        $route->post('cart/clear', [CartWebController::class, 'clear']);
        $route->get('cart/checkout', [CartWebController::class, 'checkoutForm']);
        $route->post('cart/checkout', [CartWebController::class, 'checkout']);

        $route->get('ecom-orders', [EcomOrderWebController::class, 'index']);
        $route->get('ecom-orders/{order_id}', [EcomOrderWebController::class, 'show']);

        $route->get('kyc', [UserKycWebController::class, 'index']);
        $route->post('kyc', [UserKycWebController::class, 'store']);
        $route->post('kyc/update', [UserKycWebController::class, 'update']);

        $route->middleware([WebAdminMiddleware::class], function (RouteServiceInterface $route) {
            $route->get('admin', [AdminWebController::class, 'dashboard']);
            $route->post('admin/publish-settings', [OrdersWebController::class, 'publishSettings']);

            $route->get('admin/users', [UserWebController::class, 'adminUsers']);
            $route->get('admin/users/create', [UserWebController::class, 'adminUserCreateForm']);
            $route->post('admin/users', [UserWebController::class, 'adminUserCreate']);
            $route->get('admin/users/{user_id}', [UserWebController::class, 'adminUserShow']);
            $route->post('admin/users/{user_id}', [UserWebController::class, 'adminUserUpdate']);
            $route->post('admin/users/{user_id}/delete', [UserWebController::class, 'adminUserDelete']);

            $route->get('admin/wallet/topups', [WalletWebController::class, 'adminTopups']);
            $route->post('admin/wallet/topups/{wallet_id}/approve', [WalletWebController::class, 'approveTopup']);
            $route->post('admin/wallet/topups/{wallet_id}/reject', [WalletWebController::class, 'rejectTopup']);

            $route->get('admin/batches', [BatchWebController::class, 'index']);
            $route->post('admin/batches', [BatchWebController::class, 'store']);
            $route->post('admin/batches/{batch_id}/delete', [BatchWebController::class, 'delete']);

            $route->get('admin/logs', [LogWebController::class, 'index']);
            $route->get('admin/platform-config', [PlatformConfigWebController::class, 'index']);
            $route->post('admin/platform-config', [PlatformConfigWebController::class, 'update']);
            $route->post('admin/platform-config/{platform_config_id}/delete', [PlatformConfigWebController::class, 'delete']);

            $route->get('admin/categories', [CategoryWebController::class, 'index']);
            $route->post('admin/categories', [CategoryWebController::class, 'store']);
            $route->get('admin/categories/{category_id}/edit', [CategoryWebController::class, 'editForm']);
            $route->post('admin/categories/{category_id}', [CategoryWebController::class, 'update']);
            $route->post('admin/categories/{category_id}/delete', [CategoryWebController::class, 'delete']);

            $route->get('admin/products', [ProductWebController::class, 'adminIndex']);
            $route->get('admin/products/create', [ProductWebController::class, 'adminCreateForm']);
            $route->post('admin/products', [ProductWebController::class, 'adminStore']);
            $route->get('admin/products/{product_id}/edit', [ProductWebController::class, 'adminEditForm']);
            $route->post('admin/products/{product_id}', [ProductWebController::class, 'adminUpdate']);
            $route->post('admin/products/{product_id}/delete', [ProductWebController::class, 'adminDelete']);

            $route->get('admin/ecom-orders', [EcomOrderWebController::class, 'adminIndex']);
            $route->get('admin/ecom-orders/{order_id}', [EcomOrderWebController::class, 'adminShow']);
            $route->post('admin/ecom-orders/{order_id}/assign-agent', [EcomOrderWebController::class, 'assignAgent']);
            $route->post('admin/ecom-orders/{order_id}/delivery', [EcomOrderWebController::class, 'updateDelivery']);
            $route->post('admin/ecom-orders/{order_id}/items/{order_item_id}/settle', [EcomOrderWebController::class, 'settleItem']);

            $route->get('admin/kyc', [UserKycWebController::class, 'adminIndex']);
            $route->get('admin/kyc/{kyc_id}', [UserKycWebController::class, 'adminShow']);
            $route->post('admin/kyc/{kyc_id}/approve', [UserKycWebController::class, 'approve']);
            $route->post('admin/kyc/{kyc_id}/reject', [UserKycWebController::class, 'reject']);
        });
    });
});
