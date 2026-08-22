<?php

namespace UserKyc\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
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
use UserKyc\Business\Usecases\MigrateService;
use UserKyc\Business\Usecases\RejectService;
use UserKyc\Business\Usecases\UpdateService;

class UserKycController
{
    private MigrateService $migrateService;
    private CreateService $createService;
    private UpdateService $updateService;
    private ApproveService $approveService;
    private RejectService $rejectService;
    private FetchForApprovalService $fetchForApprovalService;
    private FetchApprovedService $fetchApprovedService;
    private FetchRejectedService $fetchRejectedService;
    private FetchForUserService $fetchForUserService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        CreateService $createService,
        UpdateService $updateService,
        ApproveService $approveService,
        RejectService $rejectService,
        FetchForApprovalService $fetchForApprovalService,
        FetchApprovedService $fetchApprovedService,
        FetchRejectedService $fetchRejectedService,
        FetchForUserService $fetchForUserService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->approveService = $approveService;
        $this->rejectService = $rejectService;
        $this->fetchForApprovalService = $fetchForApprovalService;
        $this->fetchApprovedService = $fetchApprovedService;
        $this->fetchRejectedService = $fetchRejectedService;
        $this->fetchForUserService = $fetchForUserService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->createService->execute(new CreateDto(
            (int) $user->id,
            (string) $this->request->get('nin', ''),
            (string) $this->request->get('store_name', ''),
            (string) $this->request->get('description', ''),
            (array) $this->request->get('document1', []),
            (array) $this->request->get('document2', []),
            (array) $this->request->get('document3', []),
            (array) $this->request->get('document4', []),
            (array) $this->request->get('document5', [])
        ));
        $this->jsonResponseService->success([
            'kyc' => $kyc,
            'message' => 'KYC submitted successfully',
        ]);
    }

    function update()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->updateService->execute(new UpdateDto(
            (int) $this->request->get('kyc_id'),
            (int) $user->id,
            (string) $this->request->get('nin', ''),
            (string) $this->request->get('store_name', ''),
            (string) $this->request->get('description', ''),
            (array) $this->request->get('document1', []),
            (array) $this->request->get('document2', []),
            (array) $this->request->get('document3', []),
            (array) $this->request->get('document4', []),
            (array) $this->request->get('document5', [])
        ));
        $this->jsonResponseService->success([
            'kyc' => $kyc,
            'message' => 'KYC updated successfully',
        ]);
    }

    function approve()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->approveService->execute(new ApproveDto(
            (int) $this->request->get('kyc_id'),
            (int) $user->id
        ));
        $this->jsonResponseService->success([
            'kyc' => $kyc,
            'message' => 'KYC approved successfully',
        ]);
    }

    function reject()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->rejectService->execute(new RejectDto(
            (int) $this->request->get('kyc_id'),
            (int) $user->id,
            (string) $this->request->get('reject_reason', '')
        ));
        $this->jsonResponseService->success([
            'kyc' => $kyc,
            'message' => 'KYC rejected successfully',
        ]);
    }

    function fetchForApproval()
    {
        $query = $this->fetchForApprovalService->query();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Pending KYC records fetched successfully',
        ]);
    }

    function fetchApproved()
    {
        $query = $this->fetchApprovedService->query();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Approved KYC records fetched successfully',
        ]);
    }

    function fetchRejected()
    {
        $query = $this->fetchRejectedService->query();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Rejected KYC records fetched successfully',
        ]);
    }

    function fetchForUser()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->fetchForUserService->query((int) $user->id);
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'KYC records fetched successfully',
        ]);
    }
}
