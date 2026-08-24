<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;
use UserKyc\Business\Dtos\ApproveDto;
use UserKyc\Business\Dtos\CreateDto;
use UserKyc\Business\Dtos\RejectDto;
use UserKyc\Business\Dtos\UpdateDto;
use UserKyc\Business\Usecases\ApproveService;
use UserKyc\Business\Usecases\CreateService;
use UserKyc\Business\Usecases\FetchApprovedService;
use UserKyc\Business\Usecases\FetchForApprovalService;
use UserKyc\Business\Usecases\FetchForUserService;
use UserKyc\Business\Usecases\FetchRejectedService;
use UserKyc\Business\Usecases\RejectService;
use UserKyc\Business\Usecases\UpdateService;
use UserKyc\Data\UserKycRepositoryInterface;

class UserKycWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private FetchForUserService $fetchForUserService;
    private CreateService $createService;
    private UpdateService $updateService;
    private FetchForApprovalService $fetchForApprovalService;
    private FetchApprovedService $fetchApprovedService;
    private FetchRejectedService $fetchRejectedService;
    private UserKycRepositoryInterface $userKycRepository;
    private ApproveService $approveService;
    private RejectService $rejectService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        FetchForUserService $fetchForUserService,
        CreateService $createService,
        UpdateService $updateService,
        FetchForApprovalService $fetchForApprovalService,
        FetchApprovedService $fetchApprovedService,
        FetchRejectedService $fetchRejectedService,
        UserKycRepositoryInterface $userKycRepository,
        ApproveService $approveService,
        RejectService $rejectService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->fetchForUserService = $fetchForUserService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->fetchForApprovalService = $fetchForApprovalService;
        $this->fetchApprovedService = $fetchApprovedService;
        $this->fetchRejectedService = $fetchRejectedService;
        $this->userKycRepository = $userKycRepository;
        $this->approveService = $approveService;
        $this->rejectService = $rejectService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    private function userLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function adminLayout($view, $data)
    {
        $this->userLayout($view, array_merge(['layout_nav' => 'admin'], $data));
    }

    private function userKycRecord($userId)
    {
        $kyc = $this->fetchForUserService->query($userId)->fetchOne();
        return $kyc->isEmpty() ? null : $kyc;
    }

    private function userMap()
    {
        $map = [];
        $users = $this->fetchUsersAsAdminService->query([])->fetchAll();
        if (is_array($users)) {
            foreach ($users as $u) {
                $map[(int) $u->id] = $u->name;
            }
        }
        return $map;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->userKycRecord((int) $user->id);
        $this->userLayout('kyc/index', [
            'title' => 'Merchant KYC',
            'subtitle' => 'Verify your store',
            'nav' => 'kyc',
            'kyc' => $kyc,
        ]);
    }

    public function store()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->createService->execute(new CreateDto(
                (int) $user->id,
                (string) $this->request->get('nin'),
                (string) $this->request->get('store_name'),
                (string) $this->request->get('description'),
                (array) $this->request->get('document1', []),
                (array) $this->request->get('document2', []),
                (array) $this->request->get('document3', []),
                (array) $this->request->get('document4', []),
                (array) $this->request->get('document5', [])
            ));
            WebSession::flash('success', 'KYC submitted for review.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/kyc');
    }

    public function update()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->updateService->execute(new UpdateDto(
                (int) $this->request->get('kyc_id'),
                (int) $user->id,
                (string) $this->request->get('nin'),
                (string) $this->request->get('store_name'),
                (string) $this->request->get('description'),
                (array) $this->request->get('document1', []),
                (array) $this->request->get('document2', []),
                (array) $this->request->get('document3', []),
                (array) $this->request->get('document4', []),
                (array) $this->request->get('document5', [])
            ));
            WebSession::flash('success', 'KYC resubmitted for review.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/kyc');
    }

    public function adminIndex()
    {
        $tab = (string) $this->request->get('tab', 'pending');
        if ($tab === 'approved') {
            $records = $this->fetchApprovedService->query()->fetchAll();
        } elseif ($tab === 'rejected') {
            $records = $this->fetchRejectedService->query()->fetchAll();
        } else {
            $tab = 'pending';
            $records = $this->fetchForApprovalService->query()->fetchAll();
        }
        if (!is_array($records)) {
            $records = [];
        }
        $this->adminLayout('admin/user-kycs', [
            'title' => 'Merchant KYC',
            'subtitle' => 'Review submissions',
            'nav' => 'admin-kyc',
            'tab' => $tab,
            'records' => $records,
            'userMap' => $this->userMap(),
        ]);
    }

    public function adminShow()
    {
        $kycId = (int) $this->request->get('kyc_id');
        try {
            $kyc = $this->userKycRepository->find($kycId);
            if ($kyc->isEmpty()) {
                throw new \Exception('KYC record not found');
            }
            $this->adminLayout('admin/user-kyc-show', [
                'title' => 'KYC #' . $kyc->id,
                'subtitle' => $kyc->store_name,
                'nav' => 'admin-kyc',
                'kyc' => $kyc,
                'userMap' => $this->userMap(),
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/kyc');
        }
    }

    public function approve()
    {
        $kycId = (int) $this->request->get('kyc_id');
        $admin = $this->apiCredentialService->getAuthUser();
        try {
            $this->approveService->execute(new ApproveDto($kycId, (int) $admin->id));
            WebSession::flash('success', 'KYC approved.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/kyc/' . $kycId);
    }

    public function reject()
    {
        $kycId = (int) $this->request->get('kyc_id');
        $admin = $this->apiCredentialService->getAuthUser();
        try {
            $this->rejectService->execute(new RejectDto(
                $kycId,
                (int) $admin->id,
                (string) $this->request->get('reject_reason')
            ));
            WebSession::flash('success', 'KYC rejected.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/kyc/' . $kycId);
    }

}
