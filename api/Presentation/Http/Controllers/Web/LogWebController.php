<?php

namespace Presentation\Http\Controllers\Web;

use Log\Business\LogServiceInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class LogWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private LogServiceInterface $logService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        LogServiceInterface $logService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->logService = $logService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $logs = $this->logService->fetchLogs($this->request->all());
        View::render('admin/logs', [
            'title' => 'Logs',
            'subtitle' => 'System activity',
            'nav' => 'admin-logs',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'logs' => $logs->fetchAll(),
            'flash' => WebSession::pullFlash(),
        ]);
    }

}
