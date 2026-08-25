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

    private function adminLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function filters()
    {
        return [
            'type' => trim((string) $this->request->get('type', '')),
            'search' => trim((string) $this->request->get('search', '')),
        ];
    }

    public function index()
    {
        $filters = $this->filters();
        $queryFilters = array_filter($filters, function ($v) {
            return $v !== '';
        });
        $logs = $this->logService->fetchLogs($queryFilters)->fetchAll();
        if (!is_array($logs)) {
            $logs = [];
        }
        $this->adminLayout('admin/logs', [
            'title' => 'Logs',
            'subtitle' => 'System activity',
            'nav' => 'admin-logs',
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    public function show()
    {
        $logId = (int) $this->request->get('log_id');
        try {
            $log = $this->logService->find($logId);
            if ($log->isEmpty()) {
                throw new \Exception('Log not found');
            }
            $this->adminLayout('admin/log-show', [
                'title' => 'Log #' . $log->id,
                'subtitle' => $log->title,
                'nav' => 'admin-logs',
                'log' => $log,
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/logs');
        }
    }
}
