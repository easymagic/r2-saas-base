<?php

namespace Presentation\Http\Controllers\Web;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class BnplPaymentScheduleWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $filters = [
            'order_id' => trim((string) $this->request->get('order_id', '')),
            'payment_status' => trim((string) $this->request->get('payment_status', '')),
            'reference' => trim((string) $this->request->get('reference', '')),
        ];
        $queryFilters = [];
        if ($filters['order_id'] !== '') {
            $queryFilters['order_id'] = (int) $filters['order_id'];
        }
        if ($filters['payment_status'] !== '') {
            $queryFilters['payment_status'] = $filters['payment_status'];
        }
        if ($filters['reference'] !== '') {
            $queryFilters['reference'] = $filters['reference'];
        }

        $schedules = $this->bnplPaymentScheduleRepository->query($queryFilters)->fetchAll();
        if (!is_array($schedules)) {
            $schedules = [];
        }

        View::render('admin/bnpl-schedules', [
            'title' => 'BNPL schedules',
            'subtitle' => 'Installment payment schedule',
            'nav' => 'admin-bnpl',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'schedules' => $schedules,
            'filters' => $filters,
            'flash' => WebSession::pullFlash(),
        ]);
    }
}
