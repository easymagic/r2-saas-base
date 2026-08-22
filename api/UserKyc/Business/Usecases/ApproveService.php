<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use User\Data\UserRepositoryInterface;
use UserKyc\Business\Dtos\ApproveDto;
use UserKyc\Business\Usecases\Mail\SendApproveNotificationService;
use UserKyc\Data\UserKycRepositoryInterface;

class ApproveService
{
    private UserKycRepositoryInterface $userKycRepository;
    private UserRepositoryInterface $userRepository;
    private SendApproveNotificationService $sendApproveNotificationService;

    public function __construct(
        UserKycRepositoryInterface $userKycRepository,
        UserRepositoryInterface $userRepository,
        SendApproveNotificationService $sendApproveNotificationService
    ) {
        $this->userKycRepository = $userKycRepository;
        $this->userRepository = $userRepository;
        $this->sendApproveNotificationService = $sendApproveNotificationService;
    }

    public function execute(ApproveDto $approveDto)
    {
        $id = $approveDto->id;
        $approved_by = $approveDto->approved_by;

        Contracts::requires($id > 0, 'KYC ID is required');
        Contracts::requires($approved_by > 0, 'Approver ID is required');

        $kyc = $this->userKycRepository->find($id);
        Contracts::requireEntityFound($kyc, 'KYC record');
        Contracts::requires((int) $kyc->approved !== 1, 'KYC is already approved');

        $admin = $this->userRepository->find($approved_by);
        Contracts::requireEntityFound($admin, 'Approver');

        $kyc->approved = 1;
        $kyc->approved_by = $approved_by;
        $kyc->reject_reason = '';
        $kyc = $this->userKycRepository->save($kyc);

        $this->sendApproveNotificationService->execute((int) $kyc->id);

        return $kyc;
    }
}
