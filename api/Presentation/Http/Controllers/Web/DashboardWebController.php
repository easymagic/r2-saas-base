<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use SnappyOrder\Business\Usecases\GetMyOrderAsAdminService;
use SnappyOrder\Business\Usecases\GetMyOrdersAsAgentService;
use SnappyOrder\Business\Usecases\GetMyOrdersAsCustomerService;
use User\Business\Usecases\GetWalletBalanceService;

class DashboardWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private GetWalletBalanceService $getWalletBalanceService;
    private GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService;
    private GetMyOrdersAsAgentService $getMyOrdersAsAgentService;
    private GetMyOrderAsAdminService $getMyOrderAsAdminService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        GetWalletBalanceService $getWalletBalanceService,
        GetMyOrdersAsCustomerService $getMyOrdersAsCustomerService,
        GetMyOrdersAsAgentService $getMyOrdersAsAgentService,
        GetMyOrderAsAdminService $getMyOrderAsAdminService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->getMyOrdersAsCustomerService = $getMyOrdersAsCustomerService;
        $this->getMyOrdersAsAgentService = $getMyOrdersAsAgentService;
        $this->getMyOrderAsAdminService = $getMyOrderAsAdminService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $balance = $this->getWalletBalanceService->query((int) $user->id);

        if ($user->isAdmin()) {
            $query = $this->getMyOrderAsAdminService->query((int) $user->id, []);
        } elseif (strpos($user->role, 'agent') !== false) {
            $query = $this->getMyOrdersAsAgentService->query((int) $user->id, []);
        } else {
            $query = $this->getMyOrdersAsCustomerService->query((int) $user->id, []);
        }
        $orders = $query->fetchAll();
        if (!is_array($orders)) {
            $orders = [];
        }
        $orders = array_slice($orders, 0, 8);

        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'subtitle' => 'Wallet, shortcuts, and recent orders',
            'nav' => 'dashboard',
            'user' => $user,
            'balance' => $balance,
            'orders' => $orders,
            'flash' => WebSession::pullFlash(),
        ]);
    }
}
