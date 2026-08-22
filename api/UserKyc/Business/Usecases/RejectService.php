<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use User\Data\UserRepositoryInterface;
use UserKyc\Business\Dtos\RejectDto;
use UserKyc\Business\Usecases\Mail\SendRejectNotificationService;
use UserKyc\Data\UserKycRepositoryInterface;

class RejectService
{
    private UserKycRepositoryInterface $userKycRepository;
    private UserRepositoryInterface $userRepository;
    private SendRejectNotificationService $sendRejectNotificationService;

    public function __construct(
        UserKycRepositoryInterface $userKycRepository,
        UserRepositoryInterface $userRepository,
        SendRejectNotificationService $sendRejectNotificationService
    ) {
        $this->userKycRepository = $userKycRepository;
        $this->userRepository = $userRepository;
        $this->sendRejectNotificationService = $sendRejectNotificationService;
    }

    public function execute(RejectDto $rejectDto)
    {
        $id = $rejectDto->id;
        $rejected_by = $rejectDto->rejected_by;
        $reject_reason = $rejectDto->reject_reason;

        Contracts::requires($id > 0, 'KYC ID is required');
        Contracts::requires($rejected_by > 0, 'Rejector ID is required');
        Contracts::requiresNotNullOrEmpty(trim($reject_reason), 'Reject reason');

        $kyc = $this->userKycRepository->find($id);
        Contracts::requireEntityFound($kyc, 'KYC record');
        Contracts::requires((int) $kyc->approved !== 1, 'Approved KYC cannot be rejected');

        $admin = $this->userRepository->find($rejected_by);
        Contracts::requireEntityFound($admin, 'Rejector');

        $kyc->approved = 0;
        $kyc->approved_by = $rejected_by;
        $kyc->reject_reason = trim($reject_reason);
        $kyc = $this->userKycRepository->save($kyc);

        $this->sendRejectNotificationService->execute((int) $kyc->id);

        return $kyc;
    }
}
