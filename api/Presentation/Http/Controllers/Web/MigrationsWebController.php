<?php

namespace Presentation\Http\Controllers\Web;

use Batch\Business\Usecases\MigrateService as BatchMigrateService;
use BnplPaymentSchedule\Business\Usecases\MigrateService as BnplMigrateService;
use Cart\Business\Usecases\MigrateService as CartMigrateService;
use Category\Business\Usecases\MigrateService as CategoryMigrateService;
use EcomOrder\Business\Usecases\MigrateService as EcomOrderMigrateService;
use Log\Business\LogServiceInterface;
use Notification\Business\Usecases\MigrateService as NotificationMigrateService;
use OrderItem\Business\Usecases\MigrateService as OrderItemMigrateService;
use PlatformConfig\Business\Usecases\MigrateService as PlatformConfigMigrateService;
use Product\Business\Usecases\MigrateService as ProductMigrateService;
use ProxyOrderChangeLog\Business\Usecases\MigrateService as ProxyOrderChangeLogMigrateService;
use SnappyOrder\Business\Usecases\MigrateService as SnappyOrderMigrateService;
use Thread\Business\Usecases\MigrateService as ThreadMigrateService;
use User\Business\Usecases\MigrateService as UserMigrateService;
use UserKyc\Business\Usecases\MigrateService as UserKycMigrateService;
use Wallet\Business\Usecases\MigrateService as WalletMigrateService;

/**
 * Unauthenticated GET endpoint that runs every module migration.
 */
class MigrationsWebController
{
    private UserMigrateService $userMigrateService;
    private WalletMigrateService $walletMigrateService;
    private NotificationMigrateService $notificationMigrateService;
    private PlatformConfigMigrateService $platformConfigMigrateService;
    private SnappyOrderMigrateService $snappyOrderMigrateService;
    private BatchMigrateService $batchMigrateService;
    private ThreadMigrateService $threadMigrateService;
    private LogServiceInterface $logService;
    private CartMigrateService $cartMigrateService;
    private CategoryMigrateService $categoryMigrateService;
    private ProductMigrateService $productMigrateService;
    private EcomOrderMigrateService $ecomOrderMigrateService;
    private OrderItemMigrateService $orderItemMigrateService;
    private UserKycMigrateService $userKycMigrateService;
    private BnplMigrateService $bnplMigrateService;
    private ProxyOrderChangeLogMigrateService $proxyOrderChangeLogMigrateService;

    public function __construct(
        UserMigrateService $userMigrateService,
        WalletMigrateService $walletMigrateService,
        NotificationMigrateService $notificationMigrateService,
        PlatformConfigMigrateService $platformConfigMigrateService,
        SnappyOrderMigrateService $snappyOrderMigrateService,
        BatchMigrateService $batchMigrateService,
        ThreadMigrateService $threadMigrateService,
        LogServiceInterface $logService,
        CartMigrateService $cartMigrateService,
        CategoryMigrateService $categoryMigrateService,
        ProductMigrateService $productMigrateService,
        EcomOrderMigrateService $ecomOrderMigrateService,
        OrderItemMigrateService $orderItemMigrateService,
        UserKycMigrateService $userKycMigrateService,
        BnplMigrateService $bnplMigrateService,
        ProxyOrderChangeLogMigrateService $proxyOrderChangeLogMigrateService
    ) {
        $this->userMigrateService = $userMigrateService;
        $this->walletMigrateService = $walletMigrateService;
        $this->notificationMigrateService = $notificationMigrateService;
        $this->platformConfigMigrateService = $platformConfigMigrateService;
        $this->snappyOrderMigrateService = $snappyOrderMigrateService;
        $this->batchMigrateService = $batchMigrateService;
        $this->threadMigrateService = $threadMigrateService;
        $this->logService = $logService;
        $this->cartMigrateService = $cartMigrateService;
        $this->categoryMigrateService = $categoryMigrateService;
        $this->productMigrateService = $productMigrateService;
        $this->ecomOrderMigrateService = $ecomOrderMigrateService;
        $this->orderItemMigrateService = $orderItemMigrateService;
        $this->userKycMigrateService = $userKycMigrateService;
        $this->bnplMigrateService = $bnplMigrateService;
        $this->proxyOrderChangeLogMigrateService = $proxyOrderChangeLogMigrateService;
    }

    /**
     * GET /migrations — runs all module migrations and returns plain-text results.
     */
    public function run()
    {
        $steps = [
            'Users' => function () { $this->userMigrateService->execute(); },
            'Wallet' => function () { $this->walletMigrateService->execute(); },
            'Notifications' => function () { $this->notificationMigrateService->execute(); },
            'Platform configs' => function () { $this->platformConfigMigrateService->execute(); },
            'Snappy orders' => function () { $this->snappyOrderMigrateService->execute(); },
            'Batches' => function () { $this->batchMigrateService->execute(); },
            'Threads' => function () { $this->threadMigrateService->execute(); },
            'Logs' => function () { $this->logService->migrate(); },
            'Cart' => function () { $this->cartMigrateService->execute(); },
            'Categories' => function () { $this->categoryMigrateService->execute(); },
            'Products' => function () { $this->productMigrateService->execute(); },
            'Ecom orders' => function () { $this->ecomOrderMigrateService->execute(); },
            'Order items' => function () { $this->orderItemMigrateService->execute(); },
            'User KYCs' => function () { $this->userKycMigrateService->execute(); },
            'BNPL schedules' => function () { $this->bnplMigrateService->execute(); },
            'Proxy order change logs' => function () { $this->proxyOrderChangeLogMigrateService->execute(); },
        ];

        $lines = [];
        $failed = 0;

        // Framework Migration::run() echoes SQL; buffer so we can still set headers.
        ob_start();
        foreach ($steps as $label => $fn) {
            try {
                $fn();
                $lines[] = 'OK  ' . $label;
            } catch (\Exception $e) {
                $failed++;
                $lines[] = 'ERR ' . $label . ': ' . $e->getMessage();
            }
        }
        ob_end_clean();

        $lines[] = '';
        $lines[] = $failed === 0
            ? 'All migrations completed.'
            : $failed . ' migration(s) failed.';

        header('Content-Type: text/plain; charset=utf-8');
        http_response_code($failed === 0 ? 200 : 500);
        echo implode("\n", $lines) . "\n";
        exit;
    }
}
