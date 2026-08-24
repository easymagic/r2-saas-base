<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use SnappyOrder\Business\Usecases\GetMyOrderAsAdminService;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;
use Wallet\Data\WalletRepositoryInterface;

class AdminWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private GetWalletBalanceService $getWalletBalanceService;
    private GetMyOrderAsAdminService $getMyOrderAsAdminService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private WalletRepositoryInterface $walletRepository;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        GetWalletBalanceService $getWalletBalanceService,
        GetMyOrderAsAdminService $getMyOrderAsAdminService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->getMyOrderAsAdminService = $getMyOrderAsAdminService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->walletRepository = $walletRepository;
    }

    public function dashboard()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $orders = $this->getMyOrderAsAdminService->query((int) $user->id, [])->fetchAll();
        $pendingTopups = $this->walletRepository->query([
            'status' => 'pending',
            'type' => 'manual',
        ])->fetchAll();
        $usersQuery = $this->fetchUsersAsAdminService->query([]);

        View::render('admin/dashboard', [
            'title' => 'Admin',
            'subtitle' => 'Operations overview',
            'nav' => 'admin',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'order_count' => is_array($orders) ? count($orders) : 0,
            'pending_topup_count' => is_array($pendingTopups) ? count($pendingTopups) : 0,
            'user_count' => method_exists($usersQuery, 'count') ? $usersQuery->count() : 0,
            'recent_orders' => is_array($orders) ? array_slice($orders, 0, 8) : [],
            'flash' => WebSession::pullFlash(),
        ]);
    }
}
