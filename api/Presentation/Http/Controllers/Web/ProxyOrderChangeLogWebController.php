<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class ProxyOrderChangeLogWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->proxyOrderChangeLogRepository = $proxyOrderChangeLogRepository;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $filters = [
            'snappy_order_id' => trim((string) $this->request->get('snappy_order_id', '')),
            'field_name' => trim((string) $this->request->get('field_name', '')),
            'search' => trim((string) $this->request->get('search', '')),
        ];
        $queryFilters = [];
        if ($filters['snappy_order_id'] !== '') {
            $queryFilters['snappy_order_id'] = (int) $filters['snappy_order_id'];
        }
        if ($filters['field_name'] !== '') {
            $queryFilters['field_name'] = $filters['field_name'];
        }
        if ($filters['search'] !== '') {
            $queryFilters['search'] = $filters['search'];
        }

        $rows = $this->proxyOrderChangeLogRepository->query($queryFilters)->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        View::render('admin/proxy-order-change-logs', [
            'title' => 'Order change logs',
            'subtitle' => 'Snappy order field history',
            'nav' => 'admin-proxy-logs',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'rows' => $rows,
            'filters' => $filters,
            'flash' => WebSession::pullFlash(),
        ]);
    }
}
