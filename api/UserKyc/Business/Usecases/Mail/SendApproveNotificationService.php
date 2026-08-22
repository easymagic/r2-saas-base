<?php
namespace UserKyc\Business\Usecases\Mail;

use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\Usecases\CreateService as NotificationCreateService;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;
use UserKyc\Data\UserKycRepositoryInterface;

class SendApproveNotificationService
{
    private UserKycRepositoryInterface $userKycRepository;
    private UserRepositoryInterface $userRepository;
    private NotificationCreateService $notificationCreateService;
    private MailServiceInterface $mailService;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        UserKycRepositoryInterface $userKycRepository,
        UserRepositoryInterface $userRepository,
        NotificationCreateService $notificationCreateService,
        MailServiceInterface $mailService
    ) {
        $this->userKycRepository = $userKycRepository;
        $this->userRepository = $userRepository;
        $this->notificationCreateService = $notificationCreateService;
        $this->mailService = $mailService;
    }

    public function execute(int $id)
    {
        Contracts::requires($id > 0, 'KYC ID is required');

        $kyc = $this->userKycRepository->find($id);
        Contracts::requireEntityFound($kyc, 'KYC record');

        $user = $this->userRepository->find($kyc->user_id);
        Contracts::requireEntityFound($user, 'User');

        $title = 'KYC Approved';
        $message = 'Your store KYC for "' . $kyc->store_name . '" has been approved.';

        $this->notificationCreateService->execute(new NotificationCreateDto((int) $user->id, $title, $message));

        $body = 'Hello ' . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . ',<br><br>'
            . 'Your store KYC for <strong>' . htmlspecialchars($kyc->store_name, ENT_QUOTES, 'UTF-8') . '</strong> has been approved.<br><br>'
            . 'Thank you.';

        $this->mailService->send($user->email, $title, $this->fromEmail, $body);
    }
}
