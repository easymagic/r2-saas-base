<?php

namespace Presentation\Http\Controllers\Web;

use Batch\Business\Dtos\CreateDto;
use Batch\Business\Usecases\CreateService;
use Batch\Business\Usecases\GetBatchListService;
use Batch\Business\Usecases\RemoveService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class BatchWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private GetBatchListService $getBatchListService;
    private CreateService $createService;
    private RemoveService $removeService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        GetBatchListService $getBatchListService,
        CreateService $createService,
        RemoveService $removeService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->getBatchListService = $getBatchListService;
        $this->createService = $createService;
        $this->removeService = $removeService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $batches = $this->getBatchListService->query([])->fetchAll();
        View::render('admin/batches', [
            'title' => 'Batches',
            'subtitle' => 'Shipping batches',
            'nav' => 'admin-batches',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'batches' => is_array($batches) ? $batches : [],
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function store()
    {
        try {
            $this->createService->execute(new CreateDto(
                (string) $this->request->get('name'),
                (string) $this->request->get('description')
            ));
            WebSession::flash('success', 'Batch created.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/batches');
    }

    public function delete()
    {
        try {
            $this->removeService->execute((int) $this->request->get('batch_id'));
            WebSession::flash('success', 'Batch removed.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/batches');
    }

}
