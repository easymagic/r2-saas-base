<?php

namespace UserKyc\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use UserKyc\Business\Dtos\ApproveDto;
use UserKyc\Business\Dtos\CreateDto;
use UserKyc\Business\Dtos\RejectDto;
use UserKyc\Business\Dtos\UpdateDto;
use UserKyc\Business\UserKycServiceInterface;

class UserKycController
{
    private UserKycServiceInterface $userKycService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        UserKycServiceInterface $userKycService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->userKycService = $userKycService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->userKycService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $kyc = $this->userKycService->create(new CreateDto(
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
        $kyc = $this->userKycService->update(new UpdateDto(
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
        $kyc = $this->userKycService->approve(new ApproveDto(
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
        $kyc = $this->userKycService->reject(new RejectDto(
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
        $query = $this->userKycService->fetchForApproval();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Pending KYC records fetched successfully',
        ]);
    }

    function fetchApproved()
    {
        $query = $this->userKycService->fetchApproved();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Approved KYC records fetched successfully',
        ]);
    }

    function fetchRejected()
    {
        $query = $this->userKycService->fetchRejected();
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Rejected KYC records fetched successfully',
        ]);
    }

    function fetchForUser()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->userKycService->fetchForUser((int) $user->id);
        $this->jsonResponseService->success([
            'kycs' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'KYC records fetched successfully',
        ]);
    }
}
